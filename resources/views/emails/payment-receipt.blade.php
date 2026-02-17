@extends('emails.layout')

@section('title', 'Reçu de paiement')

@section('content')
<div class="greeting">
    Bonjour {{ $payment->user->first_name }},
</div>

<p>
    Merci pour votre paiement. Voici votre reçu pour la transaction effectuée sur <strong>LCP VTC</strong>.
</p>

<div class="info-box" style="border-left-color: #28a745;">
    <h3 style="margin-top: 0; color: #28a745;">💳 Reçu de paiement</h3>

    <div class="info-row">
        <span class="info-label">N° de transaction :</span>
        <span class="info-value"><strong>{{ $payment->transaction_id ?? $payment->id }}</strong></span>
    </div>

    <div class="info-row">
        <span class="info-label">Date et heure :</span>
        <span class="info-value">{{ $payment->created_at->format('d/m/Y à H:i') }}</span>
    </div>

    <div class="divider"></div>

    @if($payment->payable_type === 'App\\Models\\Ride' && $payment->payable)
    <div class="info-row">
        <span class="info-label">Type :</span>
        <span class="info-value">Paiement de course</span>
    </div>

    <div class="info-row">
        <span class="info-label">N° de course :</span>
        <span class="info-value">{{ $payment->payable->uuid }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Départ :</span>
        <span class="info-value">{{ $payment->payable->pickup_address }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Arrivée :</span>
        <span class="info-value">{{ $payment->payable->dropoff_address }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Distance :</span>
        <span class="info-value">{{ number_format($payment->payable->distance_km, 1) }} km</span>
    </div>
    @else
    <div class="info-row">
        <span class="info-label">Type :</span>
        <span class="info-value">Paiement LCP VTC</span>
    </div>
    @endif

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Montant :</span>
        <span class="info-value"><strong style="font-size: 20px; color: #667eea;">{{ number_format($payment->amount, 2) }} €</strong></span>
    </div>

    @if($payment->payment_method)
    <div class="info-row">
        <span class="info-label">Moyen de paiement :</span>
        <span class="info-value">
            @if($payment->payment_method === 'card')
                Carte bancaire
            @elseif($payment->payment_method === 'cash')
                Espèces
            @elseif($payment->payment_method === 'bank_transfer')
                Virement bancaire
            @else
                {{ ucfirst($payment->payment_method) }}
            @endif
        </span>
    </div>
    @endif

    <div class="info-row">
        <span class="info-label">Statut :</span>
        <span class="info-value" style="color: #28a745; font-weight: 600;">✓ PAYÉ</span>
    </div>
</div>

@if($payment->payable_type === 'App\\Models\\Ride' && $payment->payable)
<div class="info-box">
    <h4 style="margin-top: 0; color: #555;">📊 Détail du montant</h4>

    <div class="info-row">
        <span class="info-label">Montant HT :</span>
        <span class="info-value">{{ number_format($payment->payable->base_price ?? $payment->amount * 0.8333, 2) }} €</span>
    </div>

    <div class="info-row">
        <span class="info-label">TVA (20%) :</span>
        <span class="info-value">{{ number_format($payment->payable->tax_amount ?? $payment->amount * 0.1667, 2) }} €</span>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Total TTC :</span>
        <span class="info-value"><strong>{{ number_format($payment->amount, 2) }} €</strong></span>
    </div>
</div>
@endif

<div class="text-center mt-20">
    <a href="{{ config('app.frontend_url') }}/payments/{{ $payment->id }}" class="button">
        Voir le détail du paiement
    </a>
</div>

<div class="divider"></div>

<p class="text-muted">
    <strong>Informations légales :</strong><br>
    LCP VTC - Louer un Chauffeur Prestige<br>
    SIRET : [À compléter]<br>
    N° TVA : [À compléter]<br>
    Adresse : [À compléter]
</p>

<p class="text-muted">
    Ce document tient lieu de reçu de paiement. Conservez-le pour vos archives.
    Pour toute question concernant cette transaction, contactez notre service client.
</p>
@endsection
