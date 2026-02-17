@extends('emails.layout')

@section('title', 'Course terminée')

@section('content')
<div class="greeting">
    Bonjour {{ $ride->passenger->first_name }},
</div>

<p>
    Votre course avec <strong>LCP VTC</strong> est maintenant terminée. Nous espérons que vous avez apprécié votre trajet !
</p>

<div class="info-box">
    <h3 style="margin-top: 0; color: #667eea;">✅ Résumé de votre course</h3>

    <div class="info-row">
        <span class="info-label">N° de course :</span>
        <span class="info-value">{{ $ride->uuid }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Date :</span>
        <span class="info-value">{{ $ride->completed_at ? $ride->completed_at->format('d/m/Y à H:i') : $ride->updated_at->format('d/m/Y à H:i') }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Chauffeur :</span>
        <span class="info-value">{{ $ride->driver->first_name }} {{ $ride->driver->last_name }}</span>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Départ :</span>
        <span class="info-value">{{ $ride->pickup_address }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Arrivée :</span>
        <span class="info-value">{{ $ride->dropoff_address }}</span>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Distance parcourue :</span>
        <span class="info-value">{{ number_format($ride->actual_distance_km ?? $ride->distance_km, 1) }} km</span>
    </div>

    <div class="info-row">
        <span class="info-label">Durée :</span>
        <span class="info-value">{{ $ride->actual_duration_minutes ?? $ride->estimated_duration_minutes }} minutes</span>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">💰 Montant payé :</span>
        <span class="info-value"><strong style="font-size: 18px; color: #667eea;">{{ number_format($ride->final_price, 2) }} €</strong></span>
    </div>

    @if($ride->payment_status === 'paid')
    <div class="info-row">
        <span class="info-label">Statut du paiement :</span>
        <span class="info-value" style="color: #28a745;">✓ Payé</span>
    </div>
    @endif
</div>

<p>
    <strong>💬 Partagez votre expérience</strong>
</p>

<p>
    Votre avis est précieux pour nous ! Prenez quelques secondes pour noter votre chauffeur
    et nous aider à améliorer notre service.
</p>

<div class="text-center mt-20">
    <a href="{{ config('app.frontend_url') }}/rides/{{ $ride->uuid }}/review" class="button">
        Laisser un avis
    </a>
</div>

<div class="divider"></div>

<p class="text-muted">
    Merci d'avoir choisi <strong>LCP VTC</strong> pour votre déplacement.
    Nous espérons vous revoir très bientôt !
</p>
@endsection
