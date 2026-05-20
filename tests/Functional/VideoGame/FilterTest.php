<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class FilterTest extends FunctionalTestCase
{
    /**
     * @return iterable<array<array, int>>
     */
    public function tagsProvider(): iterable
    {
        yield 'No tags' => [[], 10];
        yield 'One tag' => [[1], 10];
        yield 'Two tags' => [[1, 2], 8];
        yield 'Multiple tags' => [[1, 2, 3, 4, 5], 2];
    }

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

    /**
     * @dataProvider tagsProvider
     */
    public function testFilterVideoGamesByTags(array $tags, int $expectedCount): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorCount(10, 'article.card.game-card');
        $this->assertSelectorTextSame('article.card.game-card:first-child span.tag:first-child', 'Tag 0');

        $form = $crawler->selectButton('Filtrer')->form();
        $form['filter[tags]'] = $tags;

        $this->assertSame('GET', $form->getMethod());
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorCount($expectedCount, 'article.card.game-card');

        $allTags = $crawler->filter('article.card.game-card span.tag')->each(fn($node) => trim($node->text()));

        foreach ($tags as $tag) {
            $expectedTag = 'Tag ' . ($tag - 1);
            $this->assertContains($expectedTag, $allTags);
        }
    }
}
