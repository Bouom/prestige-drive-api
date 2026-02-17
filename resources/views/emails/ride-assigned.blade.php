@extends('emails.layout')

@section('title', $recipient === 'driver' ? 'Nouvelle course assignée' : 'Chauffeur assigné')

@section('content')
@if($recipient === 'driver')
    <div class="greeting">
        Bonjour {{ $driver->first_name }},
    </div>

    <p>
        Une nouvelle course vous a été assignée ! Veuillez prendre connaissance des détails ci-dessous.
    </p>

    <div class="info-box">
        <h3 style="margin-top: 0; color: #667eea;">🚗 Nouvelle course</h3>

        <div class="info-row">
            <span class="info-label">N° de course :</span>
            <span class="info-value"><strong>{{ $ride->uuid }}</strong></span>
        </div>

        <div class="info-row">
            <span class="info-label">Date et heure :</span>
            <span class="info-value">{{ $ride->scheduled_at ? $ride->scheduled_at->format('d/m/Y à H:i') : 'Immédiatement' }}</span>
        </div>

        <div class="divider"></div>

        <div class="info-row">
            <span class="info-label">👤 Passager :</span>
            <span class="info-value">{{ $passenger->first_name }} {{ $passenger->last_name }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">📞 Téléphone :</span>
            <span class="info-value">{{ $passenger->phone }}</span>
        </div>

        <div class="divider"></div>

        <div class="info-row">
            <span class="info-label">🔵 Lieu de prise en charge :</span>
        </div>
        <div class="info-row" style="margin-left: 20px;">
            <span class="info-value">{{ $ride->pickup_address }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">🔴 Destination :</span>
        </div>
        <div class="info-row" style="margin-left: 20px;">
            <span class="info-value">{{ $ride->dropoff_address }}</span>
        </div>

        <div class="divider"></div>

        <div class="info-row">
            <span class="info-label">Distance :</span>
            <span class="info-value">{{ number_format($ride->distance_km, 1) }} km</span>
        </div>

        <div class="info-row">
            <span class="info-label">Durée estimée :</span>
            <span class="info-value">{{ $ride->estimated_duration_minutes }} min</span>
        </div>

        <div class="info-row">
            <span class="info-label">💰 Montant :</span>
            <span class="info-value"><strong>{{ number_format($ride->final_price, 2) }} €</strong></span>
        </div>
    </div>

    <div class="text-center mt-20">
        <a href="{{ config('app.frontend_url') }}/driver/rides/{{ $ride->uuid }}" class="button">
            Voir les détails de la course
        </a>
    </div>

    <p class="text-muted" style="margin-top: 20px;">
        <strong>N'oubliez pas :</strong>
    </p>
    <ul class="text-muted">
        <li>Contactez le client si nécessaire avant le départ</li>
        <li>Vérifiez votre véhicule avant le départ</li>
        <li>Démarrez la course dans l'application au moment du départ</li>
        <li>Terminez la course à l'arrivée pour confirmer le paiement</li>
    </ul>

@else
    <div class="greeting">
        Bonjour {{ $passenger->first_name }},
    </div>

    <p>
        Bonne nouvelle ! Un chauffeur a été assigné à votre réservation.
    </p>

    <div class="info-box">
        <h3 style="margin-top: 0; color: #667eea;">👨‍✈️ Votre chauffeur</h3>

        <div class="info-row">
            <span class="info-label">Nom :</span>
            <span class="info-value"><strong>{{ $driver->first_name }} {{ $driver->last_name }}</strong></span>
        </div>

        <div class="info-row">
            <span class="info-label">📞 Téléphone :</span>
            <span class="info-value">{{ $driver->phone }}</span>
        </div>

        @if($driver->driverProfile && $driver->driverProfile->rating)
        <div class="info-row">
            <span class="info-label">⭐ Note :</span>
            <span class="info-value">{{ number_format($driver->driverProfile->rating, 1) }}/5 ({{ $driver->driverProfile->total_rides }} courses)</span>
        </div>
        @endif

        @if($vehicle)
        <div class="divider"></div>

        <h4 style="margin: 10px 0 5px 0; color: #555;">🚗 Véhicule</h4>

        <div class="info-row">
            <span class="info-label">Marque et modèle :</span>
            <span class="info-value">{{ $vehicle->brand->name ?? '' }} {{ $vehicle->model->name ?? '' }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Couleur :</span>
            <span class="info-value">{{ $vehicle->color }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Immatriculation :</span>
            <span class="info-value"><strong>{{ $vehicle->license_plate }}</strong></span>
        </div>
        @endif
    </div>

    <div class="info-box">
        <h4 style="margin-top: 0; color: #555;">📍 Rappel de votre course</h4>

        <div class="info-row">
            <span class="info-label">N° de réservation :</span>
            <span class="info-value">{{ $ride->uuid }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Date et heure :</span>
            <span class="info-value">{{ $ride->scheduled_at ? $ride->scheduled_at->format('d/m/Y à H:i') : 'Immédiatement' }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Départ :</span>
            <span class="info-value">{{ $ride->pickup_address }}</span>
        </div>
    </div>

    <div class="text-center mt-20">
        <a href="{{ config('app.frontend_url') }}/rides/{{ $ride->uuid }}" class="button">
            Suivre ma course
        </a>
    </div>

    <p class="text-muted" style="margin-top: 20px;">
        Votre chauffeur vous contactera quelques minutes avant l'heure de prise en charge.
        Bon voyage avec LCP VTC !
    </p>
@endif
@endsection
