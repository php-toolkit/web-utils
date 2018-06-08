# php web library

## usage

### view renderer

```php
$renderer = new ViewRenderer([
    'viewsPath' => __DIR__ . '/views',
    'layout' => 'my-layout.php',
]);

echo $renderer->render('home/index');
```

- setting page attrs and add assets

```php
// before call render()
$renderer
    // page info
    ->setPageTitle($title)
    ->setPageMeta($keywords, $description)
    // assets
    ->addTopCssFile('/assets/libs/highlight/styles/default.css')
    ->addBottomJsFile([
        '/assets/libs/highlight/highlight.pack.js',
        '/assets/libs/markdown-it/markdown-it.min.js',
        '/assets/src/article/view.js'
    ]);
```

- in view template file.

```php

<!doctype html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="/assets/libs/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/src/app.css" rel="stylesheet">
  <title>Hello, world!</title>
  <!-- output page assets -->
  <?php $this->dumpTopAssets() ?>
</head>
<body>

<?php $this->include('_layouts/common-header'); ?>

<main role="main" class="container content-main">
  <div class="row">
    <div class="col-md-8 blog-main">
    <!-- content output -->
    {__CONTENT__}
    </div>
    <aside class="col-md-4">
      sadebar .... 
    </aside>
  </div>
</main>

<?php $this->include('_layouts/common-footer'); ?>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="/assets/libs/jquery.min.js"></script>
<script src="/assets/libs/bootstrap/bootstrap.min.js"></script>
<script src="/assets/src/app.js"></script>

<!-- output page assets -->
<?php $this->dumpBottomAssets() ?>

</body>
</html>
```
