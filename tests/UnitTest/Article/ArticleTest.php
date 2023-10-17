<?php

namespace App\Tests\UnitTest\Article;

use App\Entity\Article\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    private Article $article;

    /**
     * Set up a new Article object for each test.
     */
    protected function setUp(): void
    {
        $this->article = new Article();
    }

    public function testGettersAndSetters(): void
    {
        // Test Title
        $title = "Article Title";
        $this->article->setTitle($title);
        $this->assertSame($title, $this->article->getTitle());

        // Test Content
        $content = "This is the article content.";
        $this->article->setContent($content);
        $this->assertSame($content, $this->article->getContent());

        // Test CreatedAt
        $createdAt = new \DateTime();
        $this->article->setCreatedAt($createdAt);
        $this->assertSame($createdAt, $this->article->getCreatedAt());
    }

}