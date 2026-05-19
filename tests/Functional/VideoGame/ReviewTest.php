<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class ReviewTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();

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
        $this->client->request('POST', '/jeu-video-0', [
            'review' => [
                'rating' => $rating,
                'comment' => 'Jeu vidéo 0 est mon jeu de rôle préféré.',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSelectorExists('form[name="review"]');
    }
}
