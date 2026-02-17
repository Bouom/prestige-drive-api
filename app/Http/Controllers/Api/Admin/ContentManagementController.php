<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\BannerResource;
use App\Http\Resources\FaqResource;
use App\Http\Resources\NewsArticleResource;
use App\Http\Resources\PageResource;
use App\Http\Resources\PartnerResource;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\NewsArticle;
use App\Models\Page;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ContentManagementController extends BaseController
{
    // ============ PAGES ============

    /**
     * List all pages.
     */
    public function indexPages(Request $request): AnonymousResourceCollection
    {
        $query = Page::query()->with('sections');

        if ($request->filled('is_published')) {
            $query->where('status', $request->boolean('is_published') ? 'published' : 'draft');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $pages = $query->orderBy('title')->paginate(15);

        return PageResource::collection($pages);
    }

    /**
     * Store a new page.
     */
    public function storePage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Map is_published → status
        if (array_key_exists('is_published', $validated)) {
            $validated['status'] = $validated['is_published'] ? 'published' : 'draft';
            $validated['published_at'] = $validated['is_published'] ? now() : null;
            unset($validated['is_published']);
        }

        $page = Page::create($validated);

        return $this->sendResponse(
            new PageResource($page),
            'Page créée avec succès.'
        );
    }

    /**
     * Update a page.
     */
    public function updatePage(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:pages,slug,'.$page->id,
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'boolean',
        ]);

        if (array_key_exists('is_published', $validated)) {
            $validated['status'] = $validated['is_published'] ? 'published' : 'draft';
            $validated['published_at'] = $validated['is_published'] ? now() : null;
            unset($validated['is_published']);
        }

        $page->update($validated);

        return $this->sendResponse(
            new PageResource($page->load('sections')),
            'Page mise à jour avec succès.'
        );
    }

    /**
     * Delete a page.
     */
    public function destroyPage(Page $page): JsonResponse
    {
        $page->delete();

        return $this->sendResponse([], 'Page supprimée avec succès.');
    }

    // ============ NEWS ARTICLES ============

    /**
     * List all news articles.
     */
    public function indexNews(Request $request): AnonymousResourceCollection
    {
        $query = NewsArticle::query();

        if ($request->filled('is_published')) {
            $query->where('status', $request->boolean('is_published') ? 'published' : 'draft');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $articles = $query->orderBy('published_at', 'desc')
            ->paginate(15);

        return NewsArticleResource::collection($articles);
    }

    /**
     * Store a new news article.
     */
    public function storeNews(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:news_articles',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image_url' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if (array_key_exists('is_published', $validated)) {
            $validated['status'] = $validated['is_published'] ? 'published' : 'draft';
            $validated['published_at'] = $validated['is_published'] ? ($validated['published_at'] ?? now()) : null;
            unset($validated['is_published']);
        }

        // author_id is required — default to the authenticated admin
        $validated['author_id'] = $request->user()->id;

        $article = NewsArticle::create($validated);

        return $this->sendResponse(
            new NewsArticleResource($article),
            'Article créé avec succès.'
        );
    }

    /**
     * Update a news article.
     */
    public function updateNews(Request $request, NewsArticle $article): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:news_articles,slug,'.$article->id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'sometimes|string',
            'featured_image_url' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
        ]);

        if (array_key_exists('is_published', $validated)) {
            $validated['status'] = $validated['is_published'] ? 'published' : 'draft';
            $validated['published_at'] = $validated['is_published'] ? ($validated['published_at'] ?? now()) : null;
            unset($validated['is_published']);
        }

        $article->update($validated);

        return $this->sendResponse(
            new NewsArticleResource($article),
            'Article mis à jour avec succès.'
        );
    }

    /**
     * Delete a news article.
     */
    public function destroyNews(NewsArticle $article): JsonResponse
    {
        $article->delete();

        return $this->sendResponse([], 'Article supprimé avec succès.');
    }

    // ============ BANNERS ============

    /**
     * List all banners.
     */
    public function indexBanners(): AnonymousResourceCollection
    {
        $banners = Banner::orderBy('sort_order')->get();

        return BannerResource::collection($banners);
    }

    /**
     * Store a new banner.
     */
    public function storeBanner(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image_url' => 'required|string|max:500',
            'link_url' => 'nullable|string|max:500',
            'link_text' => 'nullable|string|max:100',
            'placement' => 'nullable|string|in:homepage_hero,sidebar,footer,popup',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Map frontend field names to actual DB columns
        $validated = [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'image_url' => $data['image_url'],
            'cta_url' => $data['link_url'] ?? null,
            'cta_text' => $data['link_text'] ?? null,
            'placement' => $data['placement'] ?? 'homepage_hero',
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ];

        $banner = Banner::create($validated);

        return $this->sendResponse(
            new BannerResource($banner),
            'Bannière créée avec succès.'
        );
    }

    /**
     * Update a banner.
     */
    public function updateBanner(Request $request, Banner $banner): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image_url' => 'sometimes|string|max:500',
            'link_url' => 'nullable|string|max:500',
            'link_text' => 'nullable|string|max:100',
            'placement' => 'nullable|string|in:homepage_hero,sidebar,footer,popup',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated = array_filter([
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'cta_url' => array_key_exists('link_url', $data) ? $data['link_url'] : null,
            'cta_text' => array_key_exists('link_text', $data) ? $data['link_text'] : null,
            'placement' => $data['placement'] ?? null,
            'sort_order' => $data['sort_order'] ?? null,
            'is_active' => $data['is_active'] ?? null,
        ], fn ($v) => $v !== null);

        $banner->update($validated);

        return $this->sendResponse(
            new BannerResource($banner),
            'Bannière mise à jour avec succès.'
        );
    }

    /**
     * Delete a banner.
     */
    public function destroyBanner(Banner $banner): JsonResponse
    {
        $banner->delete();

        return $this->sendResponse([], 'Bannière supprimée avec succès.');
    }

    // ============ PARTNERS ============

    /**
     * List all partners.
     */
    public function indexPartners(): AnonymousResourceCollection
    {
        $partners = Partner::orderBy('sort_order')->get();

        return PartnerResource::collection($partners);
    }

    /**
     * Store a new partner.
     */
    public function storePartner(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo_url' => 'required|string|max:500',
            'website_url' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $partner = Partner::create($validated);

        return $this->sendResponse(
            new PartnerResource($partner),
            'Partenaire créé avec succès.'
        );
    }

    /**
     * Update a partner.
     */
    public function updatePartner(Request $request, Partner $partner): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'logo_url' => 'sometimes|string|max:500',
            'website_url' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $partner->update($validated);

        return $this->sendResponse(
            new PartnerResource($partner),
            'Partenaire mis à jour avec succès.'
        );
    }

    /**
     * Delete a partner.
     */
    public function destroyPartner(Partner $partner): JsonResponse
    {
        $partner->delete();

        return $this->sendResponse([], 'Partenaire supprimé avec succès.');
    }

    // ============ FAQs ============

    /**
     * List all FAQs.
     */
    public function indexFaqs(Request $request): AnonymousResourceCollection
    {
        $query = Faq::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_published')) {
            $query->where('is_active', $request->boolean('is_published'));
        }

        $faqs = $query->orderBy('sort_order')->get();

        return FaqResource::collection($faqs);
    }

    /**
     * Store a new FAQ.
     */
    public function storeFaq(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'sort_order' => 'integer|min:0',
            'is_published' => 'boolean',
        ]);

        if (array_key_exists('is_published', $validated)) {
            $validated['is_active'] = $validated['is_published'];
            unset($validated['is_published']);
        }

        $faq = Faq::create($validated);

        return $this->sendResponse(
            new FaqResource($faq),
            'FAQ créée avec succès.'
        );
    }

    /**
     * Update a FAQ.
     */
    public function updateFaq(Request $request, Faq $faq): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'sometimes|string',
            'answer' => 'sometimes|string',
            'category' => 'nullable|string|max:100',
            'sort_order' => 'integer|min:0',
            'is_published' => 'boolean',
        ]);

        if (array_key_exists('is_published', $validated)) {
            $validated['is_active'] = $validated['is_published'];
            unset($validated['is_published']);
        }

        $faq->update($validated);

        return $this->sendResponse(
            new FaqResource($faq),
            'FAQ mise à jour avec succès.'
        );
    }

    /**
     * Delete a FAQ.
     */
    public function destroyFaq(Faq $faq): JsonResponse
    {
        $faq->delete();

        return $this->sendResponse([], 'FAQ supprimée avec succès.');
    }
}
