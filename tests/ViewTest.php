<?php

namespace slinstj\AssetsOptimizer\tests;

use yii\web\AssetManager;
use slinstj\AssetsOptimizer\View;
use Yii;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-30 at 17:45:03.
 */
class ViewTest extends TestCase
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    protected function tearDown()
    {
        parent::tearDown();
//        FileHelper::removeDirectory(Yii::getAlias('@runtime/assets'));
    }

    public function testHtmlContainsRightReferencesForAssets()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yaotests/views/index.php', ['data' => 'Hello World!']);

        $this->assertEquals(1, preg_match('#<link href="(.)*/assets/[0-9a-z]+\.css" rel="stylesheet">#', $content), 'Html view does not contain the optimized css file: ' . $content);
    }

    public function testOptimizedCssFileExists()
    {
        $view = $this->mockView();
        $content = $view->renderFile('@yaotests/views/index.php', ['data' => 'Hello World!']);
        $fileUrl = $this->findByRegex('#<link href="(.*)?" rel="stylesheet">#', $content, 1);
        $this->debug($fileUrl);
        $path = \Yii::getAlias('@webPath') . $fileUrl;

        $this->assertFileExists($path, "Expected file '$fileUrl' could not be found in '$path'.");
    }

    /**
     * @return View
     */
    protected function mockView()
    {
        return new View([
            'optimizedCssPath' => '@webPath/assets',
            'optimizedCssUrl' => '@webUrl/assets',
            'assetManager' => $this->mockAssetManager(),
        ]);
    }

    protected function mockAssetManager()
    {
        $assetDir = Yii::getAlias('@runtime/web/assets');
        if (!is_dir($assetDir)) {
            mkdir($assetDir, 0777, true);
        }

        return new AssetManager([
            'basePath' => $assetDir,
            'baseUrl' => '/assets',
        ]);
    }

    protected function findByRegex($regex, $content, $match = 1)
    {
        $matches = [];
        preg_match($regex, $content, $matches);
        return $matches[$match];
    }
}
