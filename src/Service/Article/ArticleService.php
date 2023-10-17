<?php

namespace App\Service\Article;

use App\Entity\Article\Article;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;

class ArticleService
{
    private EntityManagerInterface $entityManager;
    private ValidationService $validationService;

    public function __construct(EntityManagerInterface $entityManager, ValidationService $validationService)
    {
        $this->entityManager = $entityManager;
        $this->validationService = $validationService;
    }

    public function createArticle(Article $article): Article
    {
        $article->setCreatedAt(new \DateTime());

        $this->validationService->performEntityValidation($article, ['articleCreate']);

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $article;
    }

    public function updateArticle(Article $article, Article $modifiedArticle): Article
    {
        $article
            ->setTitle($modifiedArticle->getTitle())
            ->setContent($modifiedArticle->getContent());

        $this->validationService->performEntityValidation($article, ['articleUpdate']);

        $this->entityManager->flush();

        return $article;
    }

    public function deleteArticle(Article $article): void
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();
    }
}