<?php

namespace App\Controller\Staff\Article;

use App\Entity\Article\Article;
use App\Repository\Article\ArticleRepository;
use App\Service\Article\ArticleService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Requirement\Requirement;

#[OA\Tag('staff_articles')]
class StaffArticleController extends AbstractFOSRestController
{
    /**
     * Get a list of Articles
     *
     * @param ArticleRepository $articleRepository
     * @return array
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Article::class))
        )
    )]
    #[Rest\Get('/api/staff/articles', name: 'staff_get_articles')]
    #[Rest\View(serializerGroups: ['myArticle'])]
    public function getArticles(ArticleRepository $articleRepository): array
    {
        return $articleRepository->findAll();
    }

    /**
     * Get Article
     *
     * @param Article $article
     * @return Article
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: Article::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[Rest\Get('/api/staff/articles/{article}', name: 'staff_get_article', requirements: ['article' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ['myArticle'])]
    public function getArticle(Article $article): Article
    {
        return $article;
    }

    /**
     * Post Article
     *
     * @param Article $article
     * @param ArticleService $articleService
     * @return Article
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: Article::class)
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when entity has errors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: HttpException::class))
        )
    )]
    #[OA\Parameter(
        name: 'article',
        content: new OA\JsonContent(
            ref: new Model(type: Article::class)
        )
    )]
    #[Rest\Post('/api/staff/articles', name: 'staff_create_article')]
    #[ParamConverter('article', class: Article::class, converter: "fos_rest.request_body")]
    #[Rest\View(serializerGroups: ['myArticle'])]
    public function createArticle(Article $article, ArticleService $articleService): Article
    {
        return $articleService->createArticle($article);
    }

    /**
     * Update Article
     *
     * @param Article $article
     * @param Article $modifiedArticle
     * @param ArticleService $articleService
     * @return Article
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: Article::class)
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when entity has errors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: HttpException::class))
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[OA\Parameter(
        name: 'article',
        content: new OA\JsonContent(
            ref: new Model(type: Article::class)
        )
    )]
    #[Rest\Put('/api/staff/articles/{article}', name: 'update_article', requirements: ['article' => Requirement::UUID_V7])]
    #[ParamConverter('modifiedArticle', class: Article::class, converter: "fos_rest.request_body")]
    #[Rest\View(serializerGroups: ['myArticle'])]
    public function updateArticle(Article $article, Article $modifiedArticle, ArticleService $articleService): Article
    {
        return $articleService->updateArticle($article, $modifiedArticle);
    }

    /**
     * Delete Article
     *
     * @param Article $article
     * @param ArticleService $articleService
     * @return void
     */
    #[OA\Response(
        response: 204,
        description: 'Returned when successful'
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[Rest\Delete('/api/staff/articles/{article}', name: 'delete_article', requirements: ['id' => Requirement::UUID_V7])]
    public function deleteArticle(Article $article, ArticleService $articleService): void
    {
        $articleService->deleteArticle($article);
    }

}