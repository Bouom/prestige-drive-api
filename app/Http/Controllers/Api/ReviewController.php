<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Ride;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ReviewController extends BaseController
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Review::query()->with(['reviewer', 'reviewee', 'ride']);

        if ($request->rating) {
            $query->where('overall_rating', $request->rating);
        }

        if ($request->ride_uuid) {
            $ride = Ride::where('uuid', $request->ride_uuid)->first();
            if ($ride) {
                $query->where('ride_id', $ride->id);
            }
        }

        if ($request->driver_id) {
            $query->where('reviewee_id', $request->driver_id);
        }

        if ($request->user_id) {
            $query->where('reviewer_id', $request->user_id);
        }

        // Only show published reviews for non-admin users
        if (! $request->user()->isAdmin()) {
            $query->where('is_published', true);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);

        return ReviewResource::collection($reviews);
    }

    /**
     * Display the specified review.
     */
    public function show(int $id): ReviewResource
    {
        $review = Review::with(['reviewer', 'reviewee', 'ride'])->findOrFail($id);

        $this->authorize('view', $review);

        return new ReviewResource($review);
    }

    /**
     * Store a newly created review.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $ride = Ride::where('uuid', $request->ride_uuid)->firstOrFail();

        if ($ride->customer_id !== $request->user()->id) {
            return $this->sendError('Vous ne pouvez évaluer que les courses que vous avez effectuées.', [], 403);
        }

        if ($ride->status !== 'completed') {
            return $this->sendError('Vous ne pouvez évaluer que les courses terminées.', [], 422);
        }

        $existingReview = Review::where('ride_id', $ride->id)
            ->where('reviewer_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            return $this->sendError('Vous avez déjà évalué cette course.', [], 422);
        }

        DB::beginTransaction();
        try {
            $review = Review::create([
                'ride_id' => $ride->id,
                'reviewer_id' => $request->user()->id,
                'reviewee_id' => $ride->driver?->user_id,
                'overall_rating' => $request->overall_rating,
                'cleanliness_rating' => $request->cleanliness_rating,
                'punctuality_rating' => $request->punctuality_rating,
                'driving_quality_rating' => $request->driving_quality_rating,
                'professionalism_rating' => $request->professionalism_rating,
                'vehicle_condition_rating' => $request->vehicle_condition_rating,
                'comment' => $request->comment,
                'is_published' => false,
            ]);

            DB::commit();

            return $this->sendResponse(
                new ReviewResource($review),
                'Avis soumis avec succès.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la soumission de l\'avis.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Respond to a review (for drivers or admins).
     */
    public function respond(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $review = Review::findOrFail($id);
        $this->authorize('respond', $review);

        DB::beginTransaction();
        try {
            $review->update([
                'driver_response' => $request->response,
                'driver_responded_at' => now(),
            ]);

            DB::commit();

            return $this->sendResponse(
                new ReviewResource($review),
                'Réponse ajoutée avec succès.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de l\'ajout de la réponse.', ['error' => $e->getMessage()], 500);
        }
    }
}
