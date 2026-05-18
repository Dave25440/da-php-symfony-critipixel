<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

final class CalculateAverageRatingTest extends TestCase
{
    private VideoGame $videoGame;
    private RatingHandler $ratingHandler;

    public function setUp(): void
    {
        $this->videoGame = new VideoGame();
        $this->ratingHandler = new RatingHandler();
    }

    public function testCalculateAverageWithNoReview(): void
    {
        $this->ratingHandler->calculateAverage($this->videoGame);

        $this->assertSame(null, $this->videoGame->getAverageRating());
    }

    public function testCalculateAverageWithOneReview(): void
    {
        $this->videoGame->getReviews()->add((new Review())->setRating(5));
        $this->ratingHandler->calculateAverage($this->videoGame);

        $this->assertSame(5, $this->videoGame->getAverageRating());
    }
}
