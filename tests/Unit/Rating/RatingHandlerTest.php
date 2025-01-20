<?php

namespace App\Tests\Unit\Rating;

use App\Model\Entity\NumberOfRatingPerValue;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

class RatingHandlerTest extends TestCase
{
    /**
     * @dataProvider provideVideoGame
     * @param VideoGame $videoGame
     * @param int|null $expectedRating : expected average rating
     */
    public function testCalculateAverageRating(VideoGame $videoGame, ?int $expectedRating): void
    {
        $RatingHandler = new RatingHandler();
        $RatingHandler->calculateAverage($videoGame);
        $this->assertEquals($expectedRating, $expectedRating);
    }

    public static function provideVideoGame(): array
    {
        $numberOfReviews = random_int(5, 30);
        $average = 0;
        $rates = [];
        for ($i = 0; $i < $numberOfReviews; $i++){
            $rate = random_int(1, 5);
            $rates[] = $rate;
            $average += $rate;
        }
        return [
            'Without reviews' => [new VideoGame(), null],
            'With one review' => [self::createVideoGame([3]), 3],
            'With many random reviews'=> [self::createVideoGame($rates), intval(ceil($average/$numberOfReviews),10)],
            'With many controlled reviews' => [self::createVideoGame([1, 2, 3, 4, 5]), 3],
            'With all reviews with the same rating' => [self::createVideoGame(array_fill(0, $numberOfReviews, 3)), 3],
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

}