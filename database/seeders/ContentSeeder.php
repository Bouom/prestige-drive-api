<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Faq;
use App\Models\NewsArticle;
use App\Models\Page;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPages();
        $this->seedFaqs();
        $this->seedBanners();
        $this->seedPartners();
        $this->seedNews();

        $this->command->info('Content seeded: pages, FAQs, banners, partners, news articles.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAGES  (institutional pages matching the frontend routes)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedPages(): void
    {
        $pages = [
            [
                'title' => 'Accueil',
                'slug' => 'accueil',
                'meta_title' => 'Louer un Chauffeur Prestige – Votre chauffeur privé en France',
                'meta_description' => 'Service de chauffeur privé premium disponible 24h/24 en France et en Europe. Réservez en ligne en quelques minutes.',
                'status' => 'published',
                'published_at' => now()->subDays(365),
            ],
            [
                'title' => 'Chauffeur Privé',
                'slug' => 'chauffeur-prive',
                'meta_title' => 'Chauffeur Privé – Service disponible 24h/24 | LCP',
                'meta_description' => 'Un chauffeur professionnel conduit VOTRE véhicule. Disponible 24h/24, 7j/7 partout en France. À partir de 18,50€.',
                'status' => 'published',
                'published_at' => now()->subDays(300),
            ],
            [
                'title' => 'Conciergerie',
                'slug' => 'conciergerie',
                'meta_title' => 'Service Conciergerie Automobile | LCP',
                'meta_description' => 'Entretien, plein de carburant, lavage, contrôle technique… Confiez-nous les contraintes de votre véhicule.',
                'status' => 'published',
                'published_at' => now()->subDays(280),
            ],
            [
                'title' => 'Comment ça marche ?',
                'slug' => 'comment-ca-marche',
                'meta_title' => 'Comment fonctionne LCP ? | Louer un Chauffeur Prestige',
                'meta_description' => 'Découvrez comment réserver votre chauffeur privé en 3 étapes simples. Simulation, réservation, paiement sécurisé.',
                'status' => 'published',
                'published_at' => now()->subDays(270),
            ],
            [
                'title' => 'À propos',
                'slug' => 'a-propos',
                'meta_title' => 'À propos de Louer un Chauffeur Prestige',
                'meta_description' => "Découvrez notre histoire, nos valeurs et l'équipe derrière LCP, le service de chauffeur privé de référence en France.",
                'status' => 'published',
                'published_at' => now()->subDays(260),
            ],
            [
                'title' => 'Nos Courses',
                'slug' => 'courses',
                'meta_title' => 'Réservez votre course | Louer un Chauffeur Prestige',
                'meta_description' => 'Transferts aéroport, gare, longue distance, événements… Réservez votre course avec un chauffeur privé professionnel.',
                'status' => 'published',
                'published_at' => now()->subDays(250),
            ],
            [
                'title' => 'Nos Avis',
                'slug' => 'avis',
                'meta_title' => 'Avis clients | Louer un Chauffeur Prestige',
                'meta_description' => 'Découvrez les témoignages de nos clients satisfaits. Plus de 500 avis vérifiés sur notre service de chauffeur privé.',
                'status' => 'published',
                'published_at' => now()->subDays(240),
            ],
            [
                'title' => 'Conditions Générales de Vente',
                'slug' => 'conditions-generales-de-vente',
                'meta_title' => 'CGV | Louer un Chauffeur Prestige',
                'meta_description' => 'Conditions générales de vente du service Louer un Chauffeur Prestige.',
                'status' => 'published',
                'published_at' => now()->subDays(365),
            ],
            [
                'title' => 'Conditions Générales de Prestation',
                'slug' => 'conditions-generales-de-prestation',
                'meta_title' => 'CGP | Louer un Chauffeur Prestige',
                'meta_description' => 'Conditions générales de prestation de services du service Louer un Chauffeur Prestige.',
                'status' => 'published',
                'published_at' => now()->subDays(365),
            ],
            [
                'title' => 'Mentions Légales',
                'slug' => 'mentions-legales',
                'meta_title' => 'Mentions Légales | Louer un Chauffeur Prestige',
                'meta_description' => "Mentions légales et informations sur l'éditeur du site Louer un Chauffeur Prestige.",
                'status' => 'published',
                'published_at' => now()->subDays(365),
            ],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FAQs  (sourced from HowItWorksPage.tsx)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedFaqs(): void
    {
        $faqs = [
            // Général
            [
                'category' => 'general',
                'sort_order' => 1,
                'question' => 'Comment fonctionne le service ?',
                'answer' => 'Notre service est simple : un chauffeur professionnel conduit VOTRE véhicule. Vous gardez le confort de votre voiture tout en bénéficiant d\'un chauffeur à votre disposition. Idéal après une soirée, pour les déplacements professionnels, ou pour accompagner vos proches.',
            ],
            [
                'category' => 'general',
                'sort_order' => 2,
                'question' => 'Quels types de véhicules acceptez-vous ?',
                'answer' => 'Nous acceptons tous types de véhicules : berlines, SUV, véhicules de luxe, utilitaires légers... Nos chauffeurs sont formés pour conduire une grande variété de véhicules en toute sécurité.',
            ],
            [
                'category' => 'general',
                'sort_order' => 3,
                'question' => 'Couvrez-vous toute la France ?',
                'answer' => 'Oui, nous couvrons toute la France ainsi que l\'Europe. Que ce soit pour un trajet local ou un déplacement international, nous sommes à votre service.',
            ],
            // Réservation
            [
                'category' => 'reservation',
                'sort_order' => 4,
                'question' => 'Comment réserver un chauffeur ?',
                'answer' => 'Utilisez notre simulateur en ligne pour obtenir un devis instantané. Une fois satisfait du prix, confirmez votre réservation et procédez au paiement. Vous recevrez une confirmation par email avec tous les détails.',
            ],
            [
                'category' => 'reservation',
                'sort_order' => 5,
                'question' => 'Puis-je annuler ma réservation ?',
                'answer' => 'Oui, vous pouvez modifier ou annuler votre réservation jusqu\'à 2 heures avant l\'heure prévue (conditions variables selon votre forfait). Contactez-nous pour plus de détails.',
            ],
            [
                'category' => 'reservation',
                'sort_order' => 6,
                'question' => 'Comment suivre ma course en temps réel ?',
                'answer' => 'Depuis votre espace client, vous pouvez suivre en direct la position de votre chauffeur et recevoir des notifications à chaque étape de votre trajet.',
            ],
            // Tarifs
            [
                'category' => 'tarifs',
                'sort_order' => 7,
                'question' => 'Quels sont vos tarifs ?',
                'answer' => 'Nos tarifs commencent à partir de 18,50€. Le prix varie selon la distance, l\'heure de la course et le type de trajet. Utilisez notre simulateur pour obtenir une estimation précise.',
            ],
            [
                'category' => 'tarifs',
                'sort_order' => 8,
                'question' => 'Proposez-vous des forfaits ?',
                'answer' => 'Oui ! Nous proposons deux forfaits mensuels : le Forfait French Tech (500€/mois) et le Forfait Silicon Valley (1000€/mois). Ces forfaits offrent des avantages exclusifs et des tarifs préférentiels.',
            ],
            [
                'category' => 'tarifs',
                'sort_order' => 9,
                'question' => 'Quels modes de paiement acceptez-vous ?',
                'answer' => 'Nous acceptons les cartes bancaires (Visa, Mastercard, American Express), les virements bancaires et les paiements en espèces. Tous les paiements en ligne sont sécurisés via Stripe.',
            ],
            // Sécurité
            [
                'category' => 'securite',
                'sort_order' => 10,
                'question' => 'Les chauffeurs sont-ils assurés ?',
                'answer' => 'Absolument. Tous nos chauffeurs sont professionnels, assurés et formés. Votre véhicule est couvert pendant toute la durée du service. Votre sécurité est notre priorité.',
            ],
            [
                'category' => 'securite',
                'sort_order' => 11,
                'question' => 'Comment sont sélectionnés vos chauffeurs ?',
                'answer' => 'Chaque chauffeur passe une vérification rigoureuse : vérification du permis de conduire, des antécédents, test de conduite et entretien personnel. Seuls les meilleurs rejoignent notre réseau.',
            ],
            // Conciergerie
            [
                'category' => 'conciergerie',
                'sort_order' => 12,
                'question' => 'Qu\'est-ce que le service conciergerie ?',
                'answer' => 'Notre service conciergerie prend en charge toutes les contraintes liées à votre véhicule : entretien, plein de carburant, lavage, contrôle technique, déplacement de véhicule. Vous n\'avez plus à vous déplacer pour ces tâches.',
            ],
            [
                'category' => 'conciergerie',
                'sort_order' => 13,
                'question' => 'Puis-je utiliser la conciergerie sans être client chauffeur ?',
                'answer' => 'Oui, le service conciergerie est disponible indépendamment du service de chauffeur. Créez simplement un compte et réservez le service de votre choix.',
            ],
        ];

        foreach ($faqs as $data) {
            Faq::updateOrCreate(
                ['question' => $data['question']],
                array_merge($data, ['is_active' => true])
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BANNERS  (homepage hero + promotional banners)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedBanners(): void
    {
        $banners = [
            [
                'title' => 'Votre chauffeur privé, disponible 24h/24',
                'subtitle' => 'Un chauffeur professionnel conduit VOTRE véhicule. Confort, sécurité et prestige.',
                'image_url' => '/images/banners/hero-main.jpg',
                'cta_text' => 'Simuler mon trajet',
                'cta_url' => '/simuler',
                'placement' => 'homepage_hero',
                'sort_order' => 1,
                'is_active' => true,
                'opens_in_new_tab' => false,
            ],
            [
                'title' => 'Transferts Aéroport & Gare',
                'subtitle' => 'Paris CDG, Orly, Gare du Nord, Gare de Lyon… Arrivez à l\'heure, sans stress.',
                'image_url' => '/images/banners/airport.jpg',
                'cta_text' => 'Réserver maintenant',
                'cta_url' => '/courses',
                'placement' => 'homepage_hero',
                'sort_order' => 2,
                'is_active' => true,
                'opens_in_new_tab' => false,
            ],
            [
                'title' => 'Service Conciergerie Automobile',
                'subtitle' => 'Entretien, lavage, plein de carburant… On s\'occupe de tout pendant que vous travaillez.',
                'image_url' => '/images/banners/conciergerie.jpg',
                'cta_text' => 'Découvrir la conciergerie',
                'cta_url' => '/conciergerie',
                'placement' => 'homepage_hero',
                'sort_order' => 3,
                'is_active' => true,
                'opens_in_new_tab' => false,
            ],
            [
                'title' => 'Forfait French Tech – 500€/mois',
                'subtitle' => 'Courses illimitées, chauffeur dédié, facturation mensuelle. Idéal pour les startups.',
                'image_url' => '/images/banners/forfait-frenchtech.jpg',
                'cta_text' => 'Voir les forfaits',
                'cta_url' => '/comment-ca-marche',
                'placement' => 'sidebar',
                'sort_order' => 1,
                'is_active' => true,
                'opens_in_new_tab' => false,
            ],
            [
                'title' => 'Devenez chauffeur LCP',
                'subtitle' => 'Rejoignez notre réseau de chauffeurs professionnels et augmentez vos revenus.',
                'image_url' => '/images/banners/become-driver.jpg',
                'cta_text' => 'Rejoindre le réseau',
                'cta_url' => '/inscription',
                'placement' => 'footer',
                'sort_order' => 1,
                'is_active' => true,
                'opens_in_new_tab' => false,
            ],
        ];

        foreach ($banners as $data) {
            Banner::updateOrCreate(
                ['title' => $data['title'], 'placement' => $data['placement']],
                $data
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARTNERS
    // ─────────────────────────────────────────────────────────────────────────
    private function seedPartners(): void
    {
        $partners = [
            [
                'name' => 'AXA Assurances',
                'slug' => 'axa-assurances',
                'logo_url' => '/images/partners/axa.png',
                'website_url' => 'https://www.axa.fr',
                'description' => 'Partenaire assurance officiel de Louer un Chauffeur Prestige.',
                'category' => 'assurance',
                'is_featured' => true,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'BNP Paribas',
                'slug' => 'bnp-paribas',
                'logo_url' => '/images/partners/bnp.png',
                'website_url' => 'https://www.bnpparibas.fr',
                'description' => 'Partenaire bancaire pour les solutions de paiement entreprise.',
                'category' => 'finance',
                'is_featured' => true,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Air France',
                'slug' => 'air-france',
                'logo_url' => '/images/partners/airfrance.png',
                'website_url' => 'https://www.airfrance.fr',
                'description' => 'Partenaire transport aérien pour nos transferts aéroport.',
                'category' => 'transport',
                'is_featured' => true,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Accor Hotels',
                'slug' => 'accor-hotels',
                'logo_url' => '/images/partners/accor.png',
                'website_url' => 'https://www.accor.com',
                'description' => 'Partenaire hôtelier pour les déplacements de nos clients business.',
                'category' => 'hotellerie',
                'is_featured' => true,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'SNCF',
                'slug' => 'sncf',
                'logo_url' => '/images/partners/sncf.png',
                'website_url' => 'https://www.sncf.com',
                'description' => 'Partenaire pour les transferts gare et liaisons longue distance.',
                'category' => 'transport',
                'is_featured' => false,
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Stripe',
                'slug' => 'stripe',
                'logo_url' => '/images/partners/stripe.png',
                'website_url' => 'https://www.stripe.com',
                'description' => 'Solution de paiement sécurisé pour toutes nos transactions en ligne.',
                'category' => 'technologie',
                'is_featured' => false,
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($partners as $data) {
            Partner::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NEWS ARTICLES
    // ─────────────────────────────────────────────────────────────────────────
    private function seedNews(): void
    {
        // author_id is NOT NULL — use the admin user (or first user available)
        $authorId = User::whereHas('userType', fn ($q) => $q->where('name', 'admin'))
            ->value('id')
            ?? User::value('id');

        if (! $authorId) {
            $this->command->warn('No user found for news author_id — skipping news articles.');

            return;
        }

        $articles = [
            [
                'title' => 'LCP lance son service de conciergerie automobile à Paris',
                'slug' => 'lcp-lance-service-conciergerie-paris',
                'excerpt' => 'Louer un Chauffeur Prestige élargit son offre avec un service de conciergerie automobile complet, disponible dès maintenant à Paris et en Île-de-France.',
                'content' => "<p>Louer un Chauffeur Prestige est fier d'annoncer le lancement de son nouveau service de conciergerie automobile. Ce service innovant permet à nos clients de déléguer toutes les contraintes liées à leur véhicule.</p>\n<p>Au menu : entretien chez le garagiste, plein de carburant, lavage intérieur et extérieur, passage au contrôle technique, et bien plus encore. Nos chauffeurs se chargent de tout, pendant que vous vaquez à vos occupations.</p>\n<p>\"Nos clients nous demandaient depuis longtemps un service qui va au-delà du simple transport. La conciergerie automobile est la réponse naturelle à ce besoin\", explique le fondateur de LCP.</p>",
                'category' => 'actualites',
                'author_id' => $authorId,
                'featured_image_url' => '/images/news/conciergerie-launch.jpg',
                'status' => 'published',
                'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Comment choisir son service de chauffeur privé ?',
                'slug' => 'comment-choisir-chauffeur-prive',
                'excerpt' => "Entre VTC, taxi et chauffeur privé, il n'est pas toujours facile de s'y retrouver. Voici nos conseils pour faire le bon choix selon vos besoins.",
                'content' => "<p>Le marché du transport avec chauffeur a profondément évolué ces dernières années. VTC, taxis, chauffeurs privés… comment choisir la formule qui vous convient ?</p>\n<h2>Les critères à prendre en compte</h2>\n<p><strong>Le véhicule :</strong> Avec LCP, votre chauffeur conduit VOTRE propre véhicule. Vous gardez le confort que vous connaissez, sans surprise.</p>\n<p><strong>Le prix :</strong> Nos tarifs commencent à 18,50€, soit bien en dessous des VTC classiques pour des trajets équivalents.</p>\n<p><strong>La disponibilité :</strong> Disponibles 24h/24, 7j/7, nous nous adaptons à votre agenda, même pour les demandes de dernière minute.</p>",
                'category' => 'conseils',
                'author_id' => $authorId,
                'featured_image_url' => '/images/news/guide-chauffeur.jpg',
                'status' => 'published',
                'published_at' => now()->subDays(30),
            ],
            [
                'title' => "LCP s'étend à Lyon, Marseille et Bordeaux",
                'slug' => 'lcp-extension-lyon-marseille-bordeaux',
                'excerpt' => 'Après le succès parisien, Louer un Chauffeur Prestige est désormais disponible dans trois nouvelles grandes villes françaises.',
                'content' => "<p>Suite à une croissance remarquable en Île-de-France, Louer un Chauffeur Prestige franchit une nouvelle étape et s'implante dans trois nouvelles métropoles françaises : Lyon, Marseille et Bordeaux.</p>\n<p>Cette expansion répond à une demande croissante de nos clients professionnels qui se déplacent régulièrement entre les grandes villes. Nos partenaires chauffeurs dans ces régions ont été rigoureusement sélectionnés selon nos standards habituels.</p>",
                'category' => 'actualites',
                'author_id' => $authorId,
                'featured_image_url' => '/images/news/expansion.jpg',
                'status' => 'published',
                'published_at' => now()->subDays(45),
            ],
            [
                'title' => 'Transferts aéroport : nos conseils pour voyager sereinement',
                'slug' => 'transferts-aeroport-conseils',
                'excerpt' => "Anticiper son transfert aéroport est essentiel pour éviter le stress. Découvrez nos conseils pour arriver à l'heure à CDG, Orly ou au Bourget.",
                'content' => "<p>Les transferts aéroport représentent l'une de nos prestations les plus demandées. Et pour cause : rater son avion est l'une des pires expériences qui soit.</p>\n<h2>Anticiper, toujours anticiper</h2>\n<p>Nous recommandons de réserver votre chauffeur au minimum 24h à l'avance, en prenant en compte les éventuels embouteillages. Nos chauffeurs connaissent parfaitement les accès aux aéroports parisiens.</p>\n<p>Pour CDG, prévoyez minimum 1h30 depuis Paris intra-muros en dehors des heures de pointe, et 2h30 aux heures de pointe.</p>",
                'category' => 'conseils',
                'author_id' => $authorId,
                'featured_image_url' => '/images/news/airport-transfer.jpg',
                'status' => 'published',
                'published_at' => now()->subDays(60),
            ],
            [
                'title' => 'Nouveau : Forfait Silicon Valley pour les ETI et grands comptes',
                'slug' => 'forfait-silicon-valley-grands-comptes',
                'excerpt' => 'Notre nouveau forfait haut de gamme à 1000€/mois offre des services exclusifs aux entreprises à forts besoins en mobilité.',
                'content' => "<p>LCP lance le Forfait Silicon Valley, une offre premium destinée aux entreprises dont les collaborateurs ont des besoins intensifs en mobilité.</p>\n<h2>Ce qui est inclus</h2>\n<ul><li>Courses illimitées en France</li><li>Chauffeur dédié disponible 24h/24</li><li>Véhicule de prêt inclus</li><li>Facturation mensuelle avec rapport détaillé</li><li>Gestionnaire de compte dédié</li></ul>",
                'category' => 'produits',
                'author_id' => $authorId,
                'featured_image_url' => '/images/news/silicon-valley-package.jpg',
                'status' => 'published',
                'published_at' => now()->subDays(20),
            ],
        ];

        foreach ($articles as $data) {
            NewsArticle::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
