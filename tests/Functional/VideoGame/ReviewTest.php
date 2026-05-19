<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class ReviewTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
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
    }
}
