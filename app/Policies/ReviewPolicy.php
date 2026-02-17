<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Determine if the user can view any reviews.
     */
    public function viewAny(User $user): bool
    {
        // All users can view reviews
        return true;
    }

    /**
     * Determine if the user can view the review.
     */
    public function view(User $user, Review $review): bool
    {
        // All users can view published reviews
        // Reviewers can view their own reviews
        // Reviewed users can view their reviews
        // Admins can view any review
        return $review->is_published
            || $user->id === $review->reviewer_id
            || $user->id === $review->reviewee_id
            || $user->isAdmin();
    }

    /**
     * Determine if the user can create reviews.
     */
    public function create(User $user): bool
    {
        // All active users can create reviews
        return $user->is_active;
    }

    /**
     * Determine if the user can update the review.
     */
    public function update(User $user, Review $review): bool
    {
        // Only the reviewer can update their own review within 24 hours
        // Admins can update any review
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $review->reviewer_id
            && $review->created_at->gt(now()->subHours(24));
    }

    /**
     * Determine if the user can delete the review.
     */
    public function delete(User $user, Review $review): bool
    {
        // Reviewer can delete their own review or admins can delete any
        return $user->id === $review->reviewer_id || $user->isAdmin();
    }

    /**
     * Determine if the user can respond to the review.
     */
    public function respond(User $user, Review $review): bool
    {
        // Only the reviewed user (driver) can respond to reviews about them
        // Or admins can respond on behalf
        return $user->id === $review->reviewee_id || $user->isAdmin();
    }

    /**
     * Determine if the user can moderate the review.
     */
    public function moderate(User $user, Review $review): bool
    {
        // Only admins can moderate (publish/unpublish) reviews
        return $user->isAdmin();
    }

    /**
     * Determine if the user can flag the review.
     */
    public function flag(User $user, Review $review): bool
    {
        // Any authenticated user can flag inappropriate reviews
        return $user->is_active && $user->id !== $review->reviewer_id;
    }
}
