<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

use function array_fill_callback;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

        $videoGames = array_fill_callback(0, 500, fn (int $index): VideoGame => (new VideoGame)
            ->setTitle(sprintf('Jeu vidéo %d', $index))
            ->setDescription($this->faker->paragraphs(10, true))
            ->setReleaseDate(new DateTimeImmutable())
            ->setTest($this->faker->paragraphs(6, true))
            ->setRating(($index % 5) + 1)
            ->setImageName(sprintf('video_game_%d.png', $index))
            ->setImageSize(2_098_872)
        );

        // TODO : Ajouter les tags aux vidéos
        for ($i = 0; $i < count($videoGames); $i++) {

            for ($j = 0; $j < 5; $j++) {

                $videoGames[$i]->getTags()->add($tags[($i + $j) % count($tags)]);
            }
            $manager->persist($videoGames[$i]);
        }

        array_walk($videoGames, [$manager, 'persist']);

        $manager->flush();

        // TODO : Ajouter des reviews aux vidéos
        for ($i = 0; $i < count($videoGames); $i++) {
            for ($j = 1; $j < count($users); $j++) {
                $review = (new Review)
                    ->setRating($i % 5)
                    ->setComment($this->faker->paragraphs(3, true))
                    ->setUser($users[$j])
                    ->setVideoGame($videoGames[$i]);
                $videoGames[$i]->getReviews()->add($review);
                $manager->persist($review);
                $this->calculateAverageRating->calculateAverage($videoGames[$i]);
                $this->countRatingsPerValue->countRatingsPerValue($videoGames[$i]);
            }
        }

    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
