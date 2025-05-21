<?php
namespace App\Controllers;

use App\Models\Review;

class ReviewController extends BaseController {
    public function moderate() {
        $this->requireEmployee();
        $pendingReviews = Review::findByStatus('pending');
        $this->render('reviews/moderate', ['title' => 'Modération des avis', 'reviews' => $pendingReviews]);
    }

    public function approve($id) {
        $this->requireEmployee();
        $review = Review::findById($id);
        $review->setStatus('approved')->save();
        $this->setFlash('success', 'Avis validé');
        $this->redirect('reviews/moderate');
    }

    public function reject($id) {
        $this->requireEmployee();
        $review = Review::findById($id);
        $review->setStatus('rejected')->save();
        $this->setFlash('danger', 'Avis rejeté');
        $this->redirect('reviews/moderate');
    }
}