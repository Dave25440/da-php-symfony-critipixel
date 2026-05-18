<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Entity\NumberOfRatingPerValue;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

final class CountRatingsPerValueTest extends TestCase
{
    private VideoGame $videoGame;
    private RatingHandler $ratingHandler;

    public function setUp(): void
    {
        $this->videoGame = new VideoGame();
        $this->ratingHandler = new RatingHandler();
    }

    public function testCountRatingsPerValueWithNoReview(): void
    {
        $this->ratingHandler->countRatingsPerValue($this->videoGame);

        $this->assertEquals(new NumberOfRatingPerValue(), $this->videoGame->getNumberOfRatingsPerValue());
    }

    public function testCountRatingsPerValueWithOneReview(): void
    {
        $this->videoGame->getReviews()->add((new Review())->setRating(5));
        $this->ratingHandler->countRatingsPerValue($this->videoGame);

        $this->assertSame(1, $this->videoGame->getNumberOfRatingsPerValue()->getNumberOfFive());
    }
}
