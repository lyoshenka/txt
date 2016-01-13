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
      padding: 1rem;
      font-size: 1.8rem;
      line-height: 2.5rem;
      font-family: Arial, sans-serif;
      background-color: #001f3f;
      color: #ddd;
    }
    section {
      margin-bottom: 10rem;
    }
    pre, textarea {
      font-family: 'Courier New', Courier, 'Lucida Sans Typewriter', 'Lucida Typewriter', monospace;
    }
    pre {
      padding: 10px;
      color: #001f3f;
      background-color: #ddd;
      white-space: pre-wrap;
    }
    textarea {
      font-size: inherit;
      line-height: inherit;
      width: 100%;
      padding: 1rem;
      background-color: #fff;
    }

    a, a:visited {
      text-decoration: none;
      color: #FFDC00;
    }
    a:hover, a:active {
      text-decoration: underline;
    }

    p {
      font-size: 1.6rem;
      line-height: 2.7rem;
    }
    dl {
      margin: 0;
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
      font-size: 2rem;
    }
    h4 {
      margin: 3rem 0 1.5rem;
      font-size: 1.5rem;
      text-transform: uppercase;
    }

    button, input, select {
      border: 0;
      padding: 0.5rem 1rem;
      font-size: inherit;
      line-height: inherit;
    }
    button, input[type="checkbox"], select, label {
      cursor: pointer;
    }
    button {
      background-color: #FFDC00;
      color: #001f3f;
      font-weight: bold;
    }
    button:disabled {
      background-color: #ccc;
    }
    input[type="checkbox"] {
      height: calc(1em + 2px);
      width: calc(1em + 2px);
      margin: 0;
    }
    label input[type="checkbox"], label input[type="checkbox"] + span {
      vertical-align: middle;
    }
    form div {
      margin-bottom: 1rem;
    }

    .flash {
      position: relative;
      background-color:#FFDC00;
      color: #001f3f;
      margin: 1rem 0;
      padding: 1rem;
    }
    .flash a, .flash a:visited {
      color: #001f3f;
      text-decoration: underline;
    }
    .flash a:hover, .flash a:active {
      text-decoration: none;
    }
    .flash .close {
      cursor: pointer;
      position: absolute;
      right: 1.5rem;
      margin-left: 1rem;
    }

    .indent {
      margin-left: 3rem;
    }

    ::-webkit-input-placeholder { color: #777; } /* Safari, Chrome and Opera */
    :-moz-placeholder           { color: orange; } /* Firefox 18- */
    ::-moz-placeholder          { color: orange; } /* Firefox 19+ */
    :-ms-input-placeholder      { color: orange; } /* IE 10+ */
    ::-ms-input-placeholder     { color: orange; } /* Edge */
    :placeholder-shown          { color: #777; }
  </style>
</head>
<body>
  <h1><a href="/">TXT</a></h1>
  <?php echo $content ?>
</body>
</html>