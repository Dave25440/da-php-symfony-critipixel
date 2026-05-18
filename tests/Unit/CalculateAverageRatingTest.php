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

    /**
     * @return iterable<array<int>>
     */
    public function ratingsProvider(): iterable
    {
        yield 'Average rating of 1' => [
            1,
            1, 1,
        ];

        yield 'Average rating of 2' => [
            2,
            1, 2, 3,
        ];

        yield 'Average rating of 3' => [
            3,
            1, 2, 3, 4, 5,
        ];

        yield 'Average rating of 4' => [
            4,
            3, 4, 5,
        ];

        yield 'Average rating of 5' => [
            5,
            3, 4, 5, 5,
        ];
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

    /**
     * @dataProvider ratingsProvider
     */
    public function testCalculateAverageWithMultipleReviews(int $expectedAverageRating, int ...$ratings): void
    {
        foreach ($ratings as $rating) {
            $this->videoGame->getReviews()->add((new Review())->setRating($rating));
        }

        $this->ratingHandler->calculateAverage($this->videoGame);

        $this->assertSame($expectedAverageRating, $this->videoGame->getAverageRating());
    }
}
