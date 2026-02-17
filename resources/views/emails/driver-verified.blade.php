@extends('emails.layout')

@section('title', 'Compte chauffeur vérifié')

@section('content')
<div class="greeting">
    Bonjour {{ $driver->first_name }},
</div>

<p style="font-size: 18px; color: #28a745; font-weight: 600;">
    🎉 Félicitations ! Votre compte chauffeur a été vérifié et activé.
</p>

<p>
    Après vérification de vos documents et de vos informations, nous sommes heureux de vous confirmer
    que vous pouvez maintenant commencer à accepter des courses sur la plateforme <strong>LCP VTC</strong>.
</p>

<div class="info-box" style="border-left-color: #28a745;">
    <h3 style="margin-top: 0; color: #28a745;">✅ Votre profil chauffeur</h3>

    <div class="info-row">
        <span class="info-label">Nom complet :</span>
        <span class="info-value">{{ $driver->first_name }} {{ $driver->last_name }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Email :</span>
        <span class="info-value">{{ $driver->email }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Téléphone :</span>
        <span class="info-value">{{ $driver->phone }}</span>
    </div>

    @if($driver->driverProfile && $driver->driverProfile->license_number)
    <div class="info-row">
        <span class="info-label">N° de permis :</span>
        <span class="info-value">{{ $driver->driverProfile->license_number }}</span>
    </div>
    @endif

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Statut :</span>
        <span class="info-value" style="color: #28a745; font-weight: 600;">✓ VÉRIFIÉ ET ACTIF</span>
    </div>

    <div class="info-row">
        <span class="info-label">Date de vérification :</span>
        <span class="info-value">{{ now()->format('d/m/Y à H:i') }}</span>
    </div>
</div>

<p>
    <strong>🚀 Pour commencer à recevoir des courses :</strong>
</p>

<ol>
    <li>Connectez-vous à votre espace chauffeur</li>
    <li>Vérifiez que votre profil et vos informations de véhicule sont à jour</li>
    <li>Activez votre disponibilité pour recevoir des demandes de courses</li>
    <li>Assurez-vous que vos coordonnées bancaires sont renseignées pour recevoir vos paiements</li>
</ol>

<div class="text-center mt-20">
    <a href="{{ config('app.frontend_url') }}/driver/dashboard" class="button">
        Accéder à mon espace chauffeur
    </a>
</div>

<div class="info-box">
    <h4 style="margin-top: 0; color: #555;">💡 Conseils pour réussir</h4>

    <ul style="margin: 10px 0; padding-left: 20px;">
        <li>Maintenez un taux d'acceptation élevé</li>
        <li>Soyez ponctuel et professionnel</li>
        <li>Gardez votre véhicule propre et en bon état</li>
        <li>Communiquez clairement avec vos passagers</li>
        <li>Respectez le code de la route et les limitations de vitesse</li>
    </ul>
</div>

<div class="divider"></div>

<p class="text-muted">
    <strong>Besoin d'aide ?</strong><br>
    Consultez notre guide du chauffeur ou contactez notre équipe support.
    Nous sommes là pour vous accompagner dans votre activité !
</p>

<p>
    Bienvenue dans l'équipe <strong>LCP VTC</strong> ! 🚗
</p>
@endsection
