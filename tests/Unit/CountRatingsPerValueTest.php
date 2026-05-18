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

    private static function increaseRatings(int $one = 0, int $two = 0, int $three = 0, int $four = 0, int $five = 0): NumberOfRatingPerValue
    {
        $numberOfRatingPerValue = new NumberOfRatingPerValue();

        for ($i = 0; $i < $one; $i++) {
            $numberOfRatingPerValue->increaseOne();
        }

        for ($i = 0; $i < $two; $i++) {
            $numberOfRatingPerValue->increaseTwo();
        }

        for ($i = 0; $i < $three; $i++) {
            $numberOfRatingPerValue->increaseThree();
        }

        for ($i = 0; $i < $four; $i++) {
            $numberOfRatingPerValue->increaseFour();
        }

        for ($i = 0; $i < $five; $i++) {
            $numberOfRatingPerValue->increaseFive();
        }

        return $numberOfRatingPerValue;
    }

    /**
     * @return iterable<array{NumberOfRatingPerValue, int, int...}>
     */
    public function ratingsProvider(): iterable
    {
        yield '2 reviews rated 1' => [
            self::increaseRatings(2),
            1, 1,
        ];

        yield '3 reviews rated 1, 2 and 3' => [
            self::increaseRatings(1, 1, 1),
            1, 2, 3,
        ];

        yield '5 reviews with all possible ratings' => [
            self::increaseRatings(1, 1, 1, 1, 1),
            1, 2, 3, 4, 5,
        ];

        yield '3 reviews rated 3, 4 and 5' => [
            self::increaseRatings(0, 0, 1, 1, 1),
            3, 4, 5,
        ];

        yield '2 reviews rated 3 and 4 and 2 reviews rated 5' => [
            self::increaseRatings(0, 0, 1, 1, 2),
            3, 4, 5, 5,
        ];
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

    /**
     * @dataProvider ratingsProvider
     */
    public function testCountRatingsPerValueWithMultipleReviews(NumberOfRatingPerValue $expectedNumberOfRatingPerValue, int ...$ratings): void
    {
        foreach ($ratings as $rating) {
            $this->videoGame->getReviews()->add((new Review())->setRating($rating));
        }

        $this->ratingHandler->countRatingsPerValue($this->videoGame);

        $this->assertEquals($expectedNumberOfRatingPerValue, $this->videoGame->getNumberOfRatingsPerValue());
    }
}
