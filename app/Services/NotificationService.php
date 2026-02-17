<?php

namespace App\Services;

use App\Mail\CompanyVerifiedMail;
use App\Mail\DocumentExpiringMail;
use App\Mail\DriverVerifiedMail;
use App\Mail\PasswordResetMail;
use App\Mail\PaymentReceiptMail;
use App\Mail\RefundProcessedMail;
use App\Mail\RideAssignedMail;
use App\Mail\RideCancelledMail;
use App\Mail\RideCompletedMail;
use App\Mail\RideConfirmationMail;
use App\Mail\WelcomeMail;
use App\Models\Company;
use App\Models\Document;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Ride;
use App\Models\User;
use App\Notifications\DocumentExpiryNotification;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\PaymentNotification;
use App\Notifications\ReviewReceivedNotification;
use App\Notifications\RideStatusNotification;
use App\Notifications\VerificationNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send welcome notification to new user.
     */
    public function sendWelcomeNotification(User $user): void
    {
        try {
            // Send email
            Mail::to($user->email)->send(new WelcomeMail($user));

            // Create in-app notification
            $user->notify(new WelcomeNotification($user));

            $this->createNotification($user, [
                'type' => 'welcome',
                'title' => 'Bienvenue sur LCP VTC',
                'message' => 'Votre compte a été créé avec succès',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send password reset notification.
     */
    public function sendPasswordResetNotification(User $user, string $token): void
    {
        try {
            Mail::to($user->email)->send(new PasswordResetMail($user, $token));

            $this->createNotification($user, [
                'type' => 'password_reset',
                'title' => 'Réinitialisation de mot de passe',
                'message' => 'Un lien de réinitialisation a été envoyé à votre email',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send ride confirmation notification.
     */
    public function sendRideConfirmationNotification(Ride $ride): void
    {
        try {
            Mail::to($ride->customer->email)->send(new RideConfirmationMail($ride));

            $ride->customer->notify(new RideStatusNotification($ride, 'confirmed'));

            $this->createNotification($ride->customer, [
                'type' => 'ride_confirmed',
                'title' => 'Réservation confirmée',
                'message' => "Votre course #{$ride->uuid} a été confirmée",
                'data' => ['ride_id' => $ride->id, 'ride_uuid' => $ride->uuid],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ride confirmation notification', [
                'ride_id' => $ride->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send driver assigned notification (to both customer and driver).
     */
    public function sendDriverAssignedNotification(Ride $ride): void
    {
        try {
            // Notify customer
            Mail::to($ride->customer->email)->send(new RideAssignedMail($ride, 'passenger'));

            $ride->customer->notify(new DriverAssignedNotification($ride));

            $this->createNotification($ride->customer, [
                'type' => 'driver_assigned',
                'title' => 'Chauffeur assigné',
                'message' => "Un chauffeur a été assigné à votre course #{$ride->uuid}",
                'data' => ['ride_id' => $ride->id, 'ride_uuid' => $ride->uuid],
            ]);

            // Notify driver
            if ($ride->driver) {
                Mail::to($ride->driver->user->email)->send(new RideAssignedMail($ride, 'driver'));

                $ride->driver->user->notify(new DriverAssignedNotification($ride));

                $this->createNotification($ride->driver->user, [
                    'type' => 'ride_assigned',
                    'title' => 'Nouvelle course',
                    'message' => "Une course vous a été assignée #{$ride->uuid}",
                    'data' => ['ride_id' => $ride->id, 'ride_uuid' => $ride->uuid],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send driver assigned notification', [
                'ride_id' => $ride->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send ride completed notification.
     */
    public function sendRideCompletedNotification(Ride $ride): void
    {
        try {
            Mail::to($ride->customer->email)->send(new RideCompletedMail($ride));

            $ride->customer->notify(new RideStatusNotification($ride, 'completed'));

            $this->createNotification($ride->customer, [
                'type' => 'ride_completed',
                'title' => 'Course terminée',
                'message' => "Votre course #{$ride->uuid} est terminée",
                'data' => ['ride_id' => $ride->id, 'ride_uuid' => $ride->uuid],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ride completed notification', [
                'ride_id' => $ride->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send ride cancelled notification.
     */
    public function sendRideCancelledNotification(Ride $ride): void
    {
        try {
            Mail::to($ride->customer->email)->send(new RideCancelledMail($ride));

            $ride->customer->notify(new RideStatusNotification($ride, 'cancelled'));

            $this->createNotification($ride->customer, [
                'type' => 'ride_cancelled',
                'title' => 'Course annulée',
                'message' => "Votre course #{$ride->uuid} a été annulée",
                'data' => ['ride_id' => $ride->id, 'ride_uuid' => $ride->uuid],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ride cancelled notification', [
                'ride_id' => $ride->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send driver verified notification.
     */
    public function sendDriverVerifiedNotification(User $driver): void
    {
        try {
            Mail::to($driver->email)->send(new DriverVerifiedMail($driver));

            $driver->notify(new VerificationNotification('driver', 'verified'));

            $this->createNotification($driver, [
                'type' => 'driver_verified',
                'title' => 'Compte vérifié',
                'message' => 'Votre compte chauffeur a été vérifié et activé',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send driver verified notification', [
                'user_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send company verified notification.
     */
    public function sendCompanyVerifiedNotification(Company $company): void
    {
        try {
            Mail::to($company->contact_email)->send(new CompanyVerifiedMail($company));

            // Get company owner/admin to notify
            $owner = $company->users()->wherePivot('role', 'admin')->first();

            if ($owner) {
                $owner->notify(new VerificationNotification('company', 'verified'));

                $this->createNotification($owner, [
                    'type' => 'company_verified',
                    'title' => 'Société vérifiée',
                    'message' => "Votre société {$company->name} a été vérifiée",
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send company verified notification', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send payment receipt notification.
     */
    public function sendPaymentReceiptNotification(Payment $payment): void
    {
        try {
            Mail::to($payment->user->email)->send(new PaymentReceiptMail($payment));

            $payment->user->notify(new PaymentNotification($payment, 'success'));

            $this->createNotification($payment->user, [
                'type' => 'payment_receipt',
                'title' => 'Reçu de paiement',
                'message' => "Votre paiement de {$payment->amount}€ a été confirmé",
                'data' => ['payment_id' => $payment->id, 'amount' => $payment->amount],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment receipt notification', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send refund processed notification.
     */
    public function sendRefundProcessedNotification(Refund $refund): void
    {
        try {
            $user = $refund->payment->user;

            Mail::to($user->email)->send(new RefundProcessedMail($refund));

            $this->createNotification($user, [
                'type' => 'refund_processed',
                'title' => 'Remboursement effectué',
                'message' => "Un remboursement de {$refund->amount}€ a été traité",
                'data' => ['refund_id' => $refund->id, 'amount' => $refund->amount],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send refund processed notification', [
                'refund_id' => $refund->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send document expiring notification.
     */
    public function sendDocumentExpiringNotification(Document $document): void
    {
        try {
            $user = $document->documentable;

            if ($user instanceof User) {
                Mail::to($user->email)->send(new DocumentExpiringMail($document, $user));

                $daysUntilExpiry = (int) now()->diffInDays($document->expires_at, false);
                $user->notify(new DocumentExpiryNotification($document, $daysUntilExpiry));

                $this->createNotification($user, [
                    'type' => 'document_expiring',
                    'title' => 'Document bientôt expiré',
                    'message' => "Votre {$document->documentType->name} expire le {$document->expires_at->format('d/m/Y')}",
                    'data' => ['document_id' => $document->id],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send document expiring notification', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send review received notification.
     */
    public function sendReviewReceivedNotification(User $reviewedUser, $review): void
    {
        try {
            $reviewedUser->notify(new ReviewReceivedNotification($review));

            $this->createNotification($reviewedUser, [
                'type' => 'review_received',
                'title' => 'Nouvel avis reçu',
                'message' => 'Vous avez reçu un nouvel avis',
                'data' => ['review_id' => $review->id],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send review received notification', [
                'user_id' => $reviewedUser->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create in-app notification record.
     */
    private function createNotification(User $user, array $data): void
    {
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => $data['type'] ?? 'general',
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => $data['data'] ?? null,
        ]);
    }
}
