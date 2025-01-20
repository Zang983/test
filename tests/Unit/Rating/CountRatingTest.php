<?php

namespace App\Tests\Unit\Rating;

use App\Model\Entity\NumberOfRatingPerValue;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

class CountRatingTest extends TestCase
{

    /**
     * @dataProvider provideVideoGame
     * @param VideoGame $videoGame
     */
    public function testCountRating(VideoGame $videoGame, ?NumberOfRatingPerValue $expectedResult): void
    {
        $RatingHandler = new RatingHandler();
        $RatingHandler->countRatingsPerValue($videoGame);
        $this->assertEquals($videoGame->getNumberOfRatingsPerValue(), $expectedResult);
    }

    public static function provideVideoGame(): array
    {
        $numberOfReviews = random_int(5, 30);
        $rates = [];
        for ($i = 0; $i < $numberOfReviews; $i++) {
            $rate = random_int(1, 5);
            $rates[] = $rate;
        }
        return [
            'Without reviews' => [new VideoGame(), self::createExpectedRatingPerValue([])],
            'With one review' => [self::createVideoGame([4]), self::createExpectedRatingPerValue([4])],
            'With many random reviews'=> [self::createVideoGame($rates), self::createExpectedRatingPerValue($rates)],
            'With many controlled reviews' => [self::createVideoGame([1, 2, 3, 4, 5]), self::createExpectedRatingPerValue([1, 2, 3, 4, 5])],
            'With all reviews with the same rating' => [self::createVideoGame(array_fill(0, $numberOfReviews, 3)), self::createExpectedRatingPerValue(array_fill(0, $numberOfReviews, 3))],
        ];
    }

    private static function createVideoGame(array $reviews): VideoGame
    {
        $videoGame = new VideoGame();
        foreach ($reviews as $rating) {
            $review = new Review();
            $review->setRating($rating);
            $videoGame->getReviews()->add($review);
        }
        return $videoGame;
    }

    private static function createExpectedRatingPerValue(array $rates): NumberOfRatingPerValue
    {
        $ratingPerValue = new NumberOfRatingPerValue();
        foreach($rates as $rate)
        {
            switch ($rate) {
                case 1:
                    $ratingPerValue->increaseOne();
                    break;
                case 2:
                    $ratingPerValue->increaseTwo();
                    break;
                case 3:
                    $ratingPerValue->increaseThree();
                    break;
                case 4:
                    $ratingPerValue->increaseFour();
                    break;
                case 5:
                    $ratingPerValue->increaseFive();
                    break;
            }
        }
        return $ratingPerValue;
    }
}