<?php
namespace slinstj\AssetsOptimizer;

use MatthiasMullie\Minify;

/**
 * A modified View class capable of optimizing (minify and combine) assets bundles.
 * @author Sidney Lins (slinstj@gmail.com)
 */
class View extends \yii\web\View
{

    /** @var bool */
    public $minify = true;

    /** @var bool */
    public $combine = true;

    /** @var string path where to publish optimized css file(s) in */
    public $optimizedCssPath = '@app/web/css';

    /** @var string path where to publish optimized css file(s) in */
    public $optimizedCssUrl = '@web/css';

    /**
     * @inheritdoc
     */
    public function endPage($ajaxMode = false)
    {
        $this->trigger(self::EVENT_END_PAGE);

        $content = ob_get_clean();

        if ($this->minify === true) {
            $this->optimizeCss();
        }

        echo strtr(
            $content,
            [
                self::PH_HEAD => $this->renderHeadHtml(),
                self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
                self::PH_BODY_END => $this->renderBodyEndHtml($ajaxMode),
            ]
        );

        $this->clear();
    }

    /**
     * @return self
     */
    protected function optimizeCss()
    {
        $result = $this->minifyFiles(array_keys($this->cssFiles), 'css');

        $filename = sha1($result) . ".css";
        $finalPath = \Yii::getAlias($this->optimizedCssPath) . DIRECTORY_SEPARATOR . $filename;

        $finalUrl = sprintf('%s/%s', \Yii::getAlias($this->optimizedCssUrl), $filename);

        file_put_contents($finalPath, $result, LOCK_EX);

        $this->cssFiles[$finalPath] = \yii\helpers\Html::cssFile($finalUrl);
    }

    protected function minifyFiles($fileUrls, $type)
    {
        $min = ($type = strtolower($type)) === 'css' ? new Minify\CSS() : new Minify\JS;
        foreach ($fileUrls as $filePath) {
            $resolvedPath = $this->resolvePath($filePath);
            $min->add($resolvedPath);
            if($type === 'css') {
                unset($this->cssFiles[$filePath]);
            } else {
                unset($this->jsFiles[$filePath]);
            }
        }
        return $min->minify();
    }

    protected function resolvePath($path)
    {
        $basePath = \Yii::getAlias('@webroot');
        $baseUrl = str_replace(\Yii::getAlias('@web'), '', $path);
        $resolvedPath = realpath($basePath . DIRECTORY_SEPARATOR . $baseUrl);
        return $resolvedPath;
    }
}