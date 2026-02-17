@extends('emails.layout')

@section('title', 'Document bientôt expiré')

@section('content')
<div class="greeting">
    Bonjour {{ $user->first_name }},
</div>

<p style="color: #dc3545; font-weight: 600;">
    ⚠️ Attention : Un de vos documents arrive à expiration
</p>

<p>
    Nous vous informons que le document ci-dessous enregistré dans votre compte <strong>LCP VTC</strong>
    arrive bientôt à expiration.
</p>

<div class="info-box" style="border-left-color: #ffc107; background-color: #fff3cd;">
    <h3 style="margin-top: 0; color: #856404;">📄 Document concerné</h3>

    <div class="info-row">
        <span class="info-label">Type de document :</span>
        <span class="info-value"><strong>{{ $document->documentType->name ?? 'Document' }}</strong></span>
    </div>

    @if($document->document_number)
    <div class="info-row">
        <span class="info-label">N° de document :</span>
        <span class="info-value">{{ $document->document_number }}</span>
    </div>
    @endif

    <div class="divider"></div>

    <div class="info-row">
        <span class="info-label">Date d'émission :</span>
        <span class="info-value">{{ $document->issued_at ? $document->issued_at->format('d/m/Y') : 'Non renseignée' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Date d'expiration :</span>
        <span class="info-value" style="color: #dc3545; font-weight: 600;">
            {{ $document->expires_at ? $document->expires_at->format('d/m/Y') : 'Non renseignée' }}
        </span>
    </div>

    @if($document->expires_at)
    <div class="info-row">
        <span class="info-label">Jours restants :</span>
        <span class="info-value" style="color: #dc3545; font-weight: 600;">
            {{ $document->expires_at->diffInDays(now()) }} jours
        </span>
    </div>
    @endif
</div>

<div class="info-box" style="border-left-color: #dc3545; background-color: #f8d7da;">
    <h4 style="margin-top: 0; color: #721c24;">⚡ Action requise</h4>

    <p style="margin: 0; color: #721c24;">
        <strong>Pour continuer à utiliser nos services sans interruption, vous devez :</strong>
    </p>

    <ol style="color: #721c24; margin: 10px 0;">
        <li>Renouveler votre document auprès des autorités compétentes</li>
        <li>Télécharger le nouveau document dans votre espace personnel</li>
        <li>Attendre la validation par notre équipe</li>
    </ol>

    @if($user->user_type_id == 2) {{-- Si c'est un chauffeur --}}
    <p style="margin: 10px 0 0 0; padding: 10px; background-color: #fff; border-radius: 4px; color: #721c24;">
        <strong>⚠️ Important :</strong> Un document expiré peut entraîner la suspension de votre compte chauffeur
        et vous empêcher d'accepter de nouvelles courses.
    </p>
    @endif
</div>

<div class="text-center mt-20">
    <a href="{{ config('app.frontend_url') }}/documents/upload" class="button">
        Mettre à jour mon document
    </a>
</div>

<div class="divider"></div>

<p>
    <strong>Besoin d'aide ?</strong>
</p>

<p class="text-muted">
    Si vous avez des questions sur la procédure de renouvellement ou si vous rencontrez des difficultés
    pour télécharger votre nouveau document, notre équipe support est à votre disposition.
</p>

<p class="text-muted">
    <strong>Documents acceptés :</strong>
</p>
<ul class="text-muted">
    <li>Format : PDF, JPG, PNG</li>
    <li>Taille maximale : 5 Mo</li>
    <li>Le document doit être lisible et en couleur</li>
    <li>Toutes les informations doivent être visibles</li>
</ul>

<p>
    Merci de votre compréhension et de votre réactivité.
</p>
@endsection
