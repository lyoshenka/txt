<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    *, *:before, *:after {
      -webkit-box-sizing:border-box;
      -moz-box-sizing:border-box;
      box-sizing:border-box;
    }
    @media screen and (min-width: 850px){
      html{
        margin-left:calc(100vw - 100%);
        margin-right:0;
      }
    }
    html {
      font-size: 10px;
      -webkit-font-smoothing: antialiased;
    }
    body {
      max-width: 800px;
      margin: 0 auto;
      font-size: 1.8rem;
      font-family: Arial, sans-serif;
      color: #001f3f;
    }
    pre {
      padding: 10px;
      background-color: #eee;
    }
    p {
      font-size: 1.6rem;
      line-height: 2.7rem;
    }
    h1, h2, h3, h4 {
      font-family: 'Merriweather', serif;
      margin-top: 1.5rem;
    }
    h1 {
      font-size: 4.2rem;
      line-height: 4.5rem;
      margin-top: 3rem;
    }
    h2 {
      font-size: 2.8rem;
      line-height: 3rem;
      margin-top: 3rem;
    }
    h3 {
      font-size: 1.8rem;
    }
    h1 a {
      text-decoration: none;
      color: #0074D9;
    }
    form div {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <h1><a href="/">TXT</a></h1>
  <?php echo $content ?>
</body>
</html>