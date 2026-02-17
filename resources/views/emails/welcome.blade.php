@extends('emails.layout')

@section('title', 'Bienvenue')

@section('content')
<div class="greeting">
    Bonjour {{ $user->first_name }} {{ $user->last_name }},
</div>

<p>
    Bienvenue sur <strong>LCP VTC</strong> - Louer un Chauffeur Prestige !
</p>

<p>
    Nous sommes ravis de vous compter parmi nos membres. Votre compte a été créé avec succès
    et vous pouvez maintenant profiter de nos services de chauffeur privé de prestige.
</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Email :</span>
        <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Téléphone :</span>
        <span class="info-value">{{ $user->phone }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Date d'inscription :</span>
        <span class="info-value">{{ $user->created_at->format('d/m/Y à H:i') }}</span>
    </div>
</div>

<p>
    <strong>Prochaines étapes :</strong>
</p>
<ul>
    <li>Complétez votre profil pour une expérience personnalisée</li>
    <li>Ajoutez un moyen de paiement pour réserver plus rapidement</li>
    <li>Découvrez nos différents types de trajets et tarifs</li>
    <li>Réservez votre première course et profitez de notre service premium</li>
</ul>

<div class="text-center mt-20">
    <a href="{{ config('app.frontend_url') }}/dashboard" class="button">
        Accéder à mon compte
    </a>
</div>

<div class="divider"></div>

<p class="text-muted">
    Vous avez des questions ? N'hésitez pas à consulter notre FAQ ou à nous contacter.
    Notre équipe est là pour vous accompagner !
</p>
@endsection
