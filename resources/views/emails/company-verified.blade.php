@extends('emails.layout')

@section('title', 'Compte société vérifié')

@section('content')
<div class="greeting">
    Bonjour {{ $company->contact_first_name }},
</div>

<p style="font-size: 18px; color: #28a745; font-weight: 600;">
    🎉 Félicitations ! Votre compte société a été vérifié et activé.
</p>

<p>
    Après vérification de vos documents et de vos informations d'entreprise, nous sommes heureux
    de vous confirmer que votre société <strong>{{ $company->name }}</strong> peut maintenant
    utiliser la plateforme <strong>LCP VTC</strong>.
</p>

<div class="info-box" style="border-left-color: #28a745;">
    <h3 style="margin-top: 0; color: #28a745;">✅ Informations de votre société</h3>

    <div class="info-row">
        <span class="info-label">Raison sociale :</span>
        <span class="info-value"><strong>{{ $company->name }}</strong></span>
    </div>

    @if($company->siret)
    <div class="info-row">
        <span class="info-label">SIRET :</span>
        <span class="info-value">{{ $company->siret }}</span>
    </div>
    @endif

    @if($company->vat_number)
    <div class="info-row">
        <span class="info-label">N° TVA :</span>
        <span class="info-value">{{ $company->vat_number }}</span>
    </div>
    @endif

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Contact principal :</span>
        <span class="info-value">{{ $company->contact_first_name }} {{ $company->contact_last_name }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Email :</span>
        <span class="info-value">{{ $company->contact_email }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Téléphone :</span>
        <span class="info-value">{{ $company->contact_phone }}</span>
    </div>

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
    <strong>🚀 Prochaines étapes :</strong>
</p>

<ol>
    <li><strong>Ajoutez vos collaborateurs</strong> : Invitez les membres de votre entreprise à rejoindre votre compte</li>
    <li><strong>Configurez vos préférences</strong> : Définissez vos modes de paiement et options de facturation</li>
    <li><strong>Réservez vos premières courses</strong> : Commencez à utiliser notre service pour vos déplacements professionnels</li>
    <li><strong>Accédez au reporting</strong> : Suivez l'utilisation et les dépenses de votre entreprise</li>
</ol>

<div class="text-center mt-20">
    <a href="{{ config('app.frontend_url') }}/company/dashboard" class="button">
        Accéder à mon espace société
    </a>
</div>

<div class="info-box">
    <h4 style="margin-top: 0; color: #555;">💼 Avantages pour votre entreprise</h4>

    <ul style="margin: 10px 0; padding-left: 20px;">
        <li>Facturation centralisée mensuelle</li>
        <li>Gestion multi-utilisateurs</li>
        <li>Reporting détaillé des courses</li>
        <li>Service client dédié</li>
        <li>Tarifs préférentiels selon volume</li>
        <li>Gestion des notes de frais simplifiée</li>
    </ul>
</div>

<div class="divider"></div>

<p class="text-muted">
    <strong>Besoin d'assistance ?</strong><br>
    Notre équipe commerciale est à votre disposition pour vous accompagner dans la prise en main
    de votre compte société. N'hésitez pas à nous contacter !
</p>

<p>
    Bienvenue dans la famille <strong>LCP VTC</strong> ! 🚗
</p>
@endsection
