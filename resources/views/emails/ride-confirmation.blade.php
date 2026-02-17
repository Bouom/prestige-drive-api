@extends('emails.layout')

@section('title', 'Confirmation de réservation')

@section('content')
<div class="greeting">
    Bonjour {{ $passenger->first_name }},
</div>

<p>
    Votre réservation de course a été confirmée avec succès !
    Nous vous remercions de votre confiance.
</p>

<div class="info-box">
    <h3 style="margin-top: 0; color: #667eea;">📍 Détails de la course</h3>

    <div class="info-row">
        <span class="info-label">N° de réservation :</span>
        <span class="info-value"><strong>{{ $ride->uuid }}</strong></span>
    </div>

    <div class="info-row">
        <span class="info-label">Date et heure :</span>
        <span class="info-value">{{ $scheduledTime ? $scheduledTime->format('d/m/Y à H:i') : 'Immédiatement' }}</span>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">🔵 Départ :</span>
        <span class="info-value">{{ $pickup }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">🔴 Arrivée :</span>
        <span class="info-value">{{ $dropoff }}</span>
    </div>

    @if($ride->waypoints && $ride->waypoints->count() > 0)
    <div class="divider"></div>
    <div class="info-row">
        <span class="info-label">📌 Étapes :</span>
    </div>
    @foreach($ride->waypoints as $index => $waypoint)
    <div class="info-row" style="margin-left: 150px;">
        <span class="info-value">{{ $index + 1 }}. {{ $waypoint->address }}</span>
    </div>
    @endforeach
    @endif

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Type de trajet :</span>
        <span class="info-value">{{ $ride->tripType->name ?? 'Standard' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Distance estimée :</span>
        <span class="info-value">{{ number_format($ride->distance_km, 1) }} km</span>
    </div>

    <div class="info-row">
        <span class="info-label">Durée estimée :</span>
        <span class="info-value">{{ $ride->estimated_duration_minutes }} minutes</span>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">💰 Prix total :</span>
        <span class="info-value"><strong style="font-size: 20px; color: #667eea;">{{ number_format($totalPrice, 2) }} €</strong></span>
    </div>
</div>

<p>
    <strong>Prochaines étapes :</strong>
</p>
<ul>
    <li>Un chauffeur va être assigné à votre course</li>
    <li>Vous recevrez une notification avec les informations du chauffeur</li>
    <li>Le chauffeur vous contactera si nécessaire avant le départ</li>
</ul>

<div class="text-center mt-20">
    <a href="{{ config('app.frontend_url') }}/rides/{{ $ride->uuid }}" class="button">
        Voir ma réservation
    </a>
</div>

<div class="divider"></div>

<p class="text-muted">
    <strong>Besoin de modifier ou d'annuler ?</strong><br>
    Vous pouvez gérer votre réservation depuis votre espace client ou nous contacter directement.
</p>
@endsection
