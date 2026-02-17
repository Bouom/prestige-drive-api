@extends('emails.layout')

@section('title', 'Réinitialisation de mot de passe')

@section('content')
<div class="greeting">
    Bonjour {{ $user->first_name }},
</div>

<p>
    Vous avez demandé la réinitialisation de votre mot de passe pour votre compte <strong>LCP VTC</strong>.
</p>

<p>
    Pour créer un nouveau mot de passe, veuillez cliquer sur le bouton ci-dessous :
</p>

<div class="text-center">
    <a href="{{ $resetUrl }}" class="button">
        Réinitialiser mon mot de passe
    </a>
</div>

<div class="info-box">
    <p style="margin: 0;">
        <strong>⚠️ Important :</strong> Ce lien est valable pendant <strong>{{ $expiresIn }} minutes</strong>.
        Après ce délai, vous devrez faire une nouvelle demande.
    </p>
</div>

<p class="text-muted">
    Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
</p>
<p class="text-muted" style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px;">
    {{ $resetUrl }}
</p>

<div class="divider"></div>

<p class="text-muted">
    <strong>Vous n'avez pas demandé cette réinitialisation ?</strong><br>
    Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.
    Votre mot de passe actuel restera inchangé et votre compte est sécurisé.
</p>

<p class="text-muted">
    Pour votre sécurité, nous vous recommandons de :
</p>
<ul class="text-muted">
    <li>Utiliser un mot de passe unique et complexe</li>
    <li>Ne jamais partager vos identifiants</li>
    <li>Nous signaler toute activité suspecte</li>
</ul>
@endsection
