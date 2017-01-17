<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Zend Framework Default Application</title>
</head>
<body>
<div class="container_12">
<div class="bottom-spacing errow-mass">
  <h1>An error occurred</h1>
  <h2><?php echo $this->message ?></h2>

  <?php if ('development' == APPLICATION_ENV): ?>

  <h3>Exception information:</h3>
  <p class="error-box">
      <b>Message:</b> <?php echo $this->exception->getMessage() ?>
  </p>
<p class="error-box">
  <h3>Stack trace:</h3>
  <pre><?php echo $this->exception->getTraceAsString() ?>
  </pre>
</p>
<p class="error-box">
  <h3>Request Parameters:</h3>
  <pre><?php echo var_export($this->request->getParams(), true) ?>
  </pre>
  </p>
  <?php endif ?>
</div></div>
</body>
</html>
