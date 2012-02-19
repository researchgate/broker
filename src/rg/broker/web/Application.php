<?php
namespace rg\broker\web;

class Application {

    /**
     * @var \Slim
     */
    private $app;

    public function run() {
        $that = $this;

        $app = new \Slim(array(
            'view' => 'rg\broker\web\View',
            'templates.path' => './src/rg/broker/web/templates',
        ));

        $this->app = $app;

        $app->get('/', function() use($app, $that) {
            /** @var \Slim $app */
            /** @var Application $that  */
            $mdParser = new \dflydev\markdown\MarkdownParser();
            $content = $mdParser->transformMarkdown(file_get_contents(ROOT . '/README.md'));
            $app->render('home.php', array(
                'repositories' => $that->getRepositories(),
                'content' => $content
            ));
        });

        $app->get('/repositories/:repositoryName/', function($repositoryName) use($app, $that) {
            /** @var \Slim $app */
            /** @var Application $that  */
            $app->render('repository.php', array(
                'repositories' => $that->getRepositories(),
                'currentRepository' => $that->getRepository($repositoryName),
            ));
        });

        $app->get('/repositories/:repositoryName/package/:packageName', function($repositoryName, $packageName) use($app, $that) {
            /** @var \Slim $app */
            /** @var Application $that  */
            $packageName = urldecode($packageName);
            $app->render('package.php', array(
                'repositories' => $that->getRepositories(),
                'currentRepository' => $that->getRepository($repositoryName),
                'package' => $that->getPackage($repositoryName, $packageName)
            ));
        });

        $app->run();
    }

    /**
     * @return array
     */
    public function getRepositories() {
        $repositoryBrowser = new \rg\broker\repositories\RepositoryBrowser();
        return $repositoryBrowser->getRepositories();
    }

    /**
     * @param string $repositoryName
     * @return \rg\broker\repositories\Repository
     */
    public function getRepository($repositoryName) {
        if (!file_exists(ROOT . '/repositories/' . $repositoryName)) {
            $this->app->redirect(ROOTURL);
        }
        return new \rg\broker\repositories\Repository($repositoryName);
    }

    /**
     * @param string $repositoryName
     * @param string $packageName
     * @return \Composer\Package\PackageInterface
     */
    public function getPackage($repositoryName, $packageName) {
        $repository = $this->getRepository($repositoryName);
        $packages = $repository->findPackagesByName($packageName);
        if (!$packages) {
            $this->app->redirect(ROOTURL . '/' . $repositoryName);
        }
        return array_shift($packages);
    }
}