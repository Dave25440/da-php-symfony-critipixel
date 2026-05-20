<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class FilterTest extends FunctionalTestCase
{
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    public function testFilterVideoGamesByTags(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorCount(10, 'article.card.game-card');
        $this->assertSelectorTextSame('article.card.game-card:first-child span.tag:first-child', 'Tag 0');

        $form = $crawler->selectButton('Filtrer')->form();
        $form['filter[tags][0]'] = 1;

        $this->assertSame('GET', $form->getMethod());
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorCount(10, 'article.card.game-card');
        $this->assertSelectorTextSame('article.card.game-card:first-child span.tag:first-child', 'Tag 0');
        $this->assertSelectorTextSame('article.card.game-card:last-child span.tag:first-child', 'Tag 0');
    }
}
