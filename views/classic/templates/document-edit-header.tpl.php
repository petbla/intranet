<!DOCTYPE html>
<html lang="cs-CZ">
  <head>
    <meta charset="utf-8" />
    <title>{cfg_sitename}</title>
    <link href="views/classic/styles/default.css" rel="stylesheet" type="text/css"> 
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" rel="stylesheet"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/onBeforeScripts.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>   
    <!-- Quill Image Resize Module JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quill-image-resize-module/3.0.0/image-resize.min.js"></script>
    <!-- jsPDF JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- html2canvas JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- html2pdf JS -->
    <script src="https://rawgit.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>
		<script
			src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
			integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
			crossorigin="anonymous"
			referrerpolicy="no-referrer"
		></script>
    <style>
      #editor {
        width: 100%;
        min-height: 100px;
        resize: vertical;
      }
    </style>

  </head>
  <body>    
    <div id="setup" webroot="{cfg_webroot}" style="display:none;"></div>
    <div id="msg" ></div>
    <div id="envelope">


        