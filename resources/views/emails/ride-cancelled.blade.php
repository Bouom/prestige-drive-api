@extends('emails.layout')

@section('title', 'Course annulée')

@section('content')
<div class="greeting">
    Bonjour {{ $ride->passenger->first_name }},
</div>

<p>
    Votre réservation de course <strong>#{{ $ride->uuid }}</strong> a été annulée.
</p>

<div class="info-box">
    <h3 style="margin-top: 0; color: #dc3545;">❌ Détails de l'annulation</h3>

    <div class="info-row">
        <span class="info-label">N° de course :</span>
        <span class="info-value">{{ $ride->uuid }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Date d'annulation :</span>
        <span class="info-value">{{ $ride->cancelled_at ? $ride->cancelled_at->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i') }}</span>
    </div>

    @if($ride->cancellation_reason)
    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Motif :</span>
    </div>
    <div class="info-row">
        <span class="info-value" style="display: block; margin-top: 5px; padding: 10px; background-color: #fff3cd; border-radius: 4px;">
            {{ $ride->cancellation_reason }}
        </span>
    </div>
    @endif

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Annulée par :</span>
        <span class="info-value">{{ $ride->cancelled_by === 'passenger' ? 'Vous-même' : ($ride->cancelled_by === 'driver' ? 'Le chauffeur' : 'LCP VTC') }}</span>
    </div>
</div>

@if($ride->payment_status === 'paid' || $ride->payment_status === 'refunded')
<div class="info-box" style="border-left-color: #28a745;">
    <h4 style="margin-top: 0; color: #28a745;">💳 Remboursement</h4>

    @if($ride->payment_status === 'refunded')
    <p style="margin: 0;">
        ✓ Le montant de <strong>{{ number_format($ride->final_price, 2) }} €</strong> a été remboursé sur votre moyen de paiement.
        Le remboursement apparaîtra sur votre compte sous 5 à 10 jours ouvrés.
    </p>
    @else
    <p style="margin: 0;">
        Le remboursement de <strong>{{ number_format($ride->final_price, 2) }} €</strong> est en cours de traitement.
        Vous recevrez une confirmation par email une fois le remboursement effectué.
    </p>
    @endif
</div>
@endif

<p>
    <strong>Besoin de réserver à nouveau ?</strong>
</p>

<p>
    Nous serions ravis de vous accueillir à nouveau pour un prochain trajet.
    Notre service est disponible 24h/24 et 7j/7 pour tous vos déplacements.
</p>

<div class="text-center mt-20">
    <a href="{{ config('app.frontend_url') }}/rides/new" class="button">
        Réserver une nouvelle course
    </a>
</div>

<div class="divider"></div>

<p class="text-muted">
    Si vous avez des questions concernant cette annulation, n'hésitez pas à nous contacter.
    Notre équipe est à votre disposition pour vous assister.
</p>
@endsection
