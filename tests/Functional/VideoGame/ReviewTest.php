<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class ReviewTest extends WebTestCase
{
    private ?KernelBrowser $client;
    private ?EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    private function loginUser(): void
    {
        $userRepository = $this->client->getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneByEmail('user+5@email.com');

        $this->client->loginUser($user);
    }

    /**
     * @return iterable<array{?int}>
     */
    public function ratingProvider(): iterable
    {
        yield 'No rating' => [null];
        yield 'Rating outside the range' => [6];
    }

    public function testPostReview(): void
    {
        $this->loginUser();

        $crawler = $this->client->request('GET', '/jeu-video-0');

        $form = $crawler->selectButton('Poster')->form();
        $form['review[rating]'] = 5;
        $form['review[comment]'] = 'Jeu vidéo 0 est mon jeu de rôle préféré.';

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.list-group-item:last-child span.value', '5');
        $this->assertSelectorTextContains('div.list-group-item:last-child h3', 'user+5');
        $this->assertSelectorTextContains('div.list-group-item:last-child p', 'Jeu vidéo 0 est mon jeu de rôle préféré.');
        $this->assertSelectorNotExists('form[name="review"]');
    }

    /**
     * @dataProvider ratingProvider
     */
    public function testPostReviewWithInvalidRating(?int $rating = null): void
    {
        $this->loginUser();

        $this->client->request('POST', '/jeu-video-0', [
            'review' => [
                'rating' => $rating,
                'comment' => 'Jeu vidéo 0 est mon jeu de rôle préféré.',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSelectorExists('form[name="review"]');
    }

    public function testPostReviewUnauthenticated(): void
    {
        $initialReviews = $this->entityManager->getRepository(Review::class)->count([]);

        $this->client->request('POST', '/jeu-video-0', [
            'review' => [
                'rating' => 5,
                'comment' => 'Jeu vidéo 0 est mon jeu de rôle préféré.',
            ],
        ]);

        $finalReviews = $this->entityManager->getRepository(Review::class)->count([]);

        $this->assertSame($initialReviews, $finalReviews);
    }

    public function testFormInvisibleToAnonymous(): void
    {
        $crawler = $this->client->request('GET', '/jeu-video-0');

        $this->assertSelectorNotExists('form[name="review"]');
    }

    public function tearDown(): void
    {
        $review = $this->entityManager
            ->getRepository(Review::class)
            ->findOneBy([
                'rating' => 5,
                'comment' => 'Jeu vidéo 0 est mon jeu de rôle préféré.',
            ]);

        if ($review) {
            $this->entityManager->remove($review);
            $this->entityManager->flush();
            $review = null;
        }

        parent::tearDown();

        $this->entityManager = null;
        $this->client = null;
    }
}
