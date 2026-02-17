@extends('emails.layout')

@section('title', 'Remboursement effectué')

@section('content')
<div class="greeting">
    Bonjour {{ $refund->user->first_name }},
</div>

<p>
    Votre demande de remboursement a été traitée avec succès.
</p>

<div class="info-box" style="border-left-color: #28a745;">
    <h3 style="margin-top: 0; color: #28a745;">💰 Remboursement effectué</h3>

    <div class="info-row">
        <span class="info-label">N° de remboursement :</span>
        <span class="info-value"><strong>{{ $refund->id }}</strong></span>
    </div>

    <div class="info-row">
        <span class="info-label">Date de traitement :</span>
        <span class="info-value">{{ $refund->processed_at ? $refund->processed_at->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i') }}</span>
    </div>

    <div class="divider"></div>

    @if($refund->payment)
    <div class="info-row">
        <span class="info-label">Transaction d'origine :</span>
        <span class="info-value">{{ $refund->payment->transaction_id ?? $refund->payment->id }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Date du paiement :</span>
        <span class="info-value">{{ $refund->payment->created_at->format('d/m/Y') }}</span>
    </div>
    @endif

    @if($refund->payment && $refund->payment->payable_type === 'App\\Models\\Ride' && $refund->payment->payable)
    <div class="info-row">
        <span class="info-label">Course concernée :</span>
        <span class="info-value">{{ $refund->payment->payable->uuid }}</span>
    </div>
    @endif

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Montant remboursé :</span>
        <span class="info-value"><strong style="font-size: 20px; color: #28a745;">{{ number_format($refund->amount, 2) }} €</strong></span>
    </div>

    @if($refund->refund_method)
    <div class="info-row">
        <span class="info-label">Mode de remboursement :</span>
        <span class="info-value">
            @if($refund->refund_method === 'card')
                Carte bancaire originale
            @elseif($refund->refund_method === 'bank_transfer')
                Virement bancaire
            @elseif($refund->refund_method === 'wallet')
                Crédit compte client
            @else
                {{ ucfirst($refund->refund_method) }}
            @endif
        </span>
    </div>
    @endif

    <div class="info-row">
        <span class="info-label">Statut :</span>
        <span class="info-value" style="color: #28a745; font-weight: 600;">✓ REMBOURSÉ</span>
    </div>
</div>

@if($refund->reason)
<div class="info-box">
    <h4 style="margin-top: 0; color: #555;">📝 Motif du remboursement</h4>

    <p style="margin: 0; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">
        {{ $refund->reason }}
    </p>
</div>
@endif

<div class="info-box" style="border-left-color: #ffc107; background-color: #fff3cd;">
    <h4 style="margin-top: 0; color: #856404;">⏱️ Délai de traitement</h4>

    <p style="margin: 0; color: #856404;">
        Le remboursement a été initié et devrait apparaître sur votre compte
        sous <strong>5 à 10 jours ouvrés</strong>, selon votre établissement bancaire.
    </p>
</div>

<div class="text-center mt-20">
    <a href="{{ config('app.frontend_url') }}/refunds/{{ $refund->id }}" class="button">
        Voir le détail du remboursement
    </a>
</div>

<div class="divider"></div>

<p class="text-muted">
    <strong>Le montant n'apparaît pas sur votre compte ?</strong><br>
    Si après 10 jours ouvrés le remboursement n'est toujours pas visible, veuillez :
</p>
<ul class="text-muted">
    <li>Vérifier auprès de votre banque</li>
    <li>Nous contacter avec votre numéro de remboursement</li>
</ul>

<p>
    Nous vous remercions de votre patience et restons à votre disposition pour toute question.
</p>
@endsection
