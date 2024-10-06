<!DOCTYPE html>
<html lang="cs-CZ">
  <head>
    <meta charset="utf-8" />
    <title>{cfg_sitename}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <style>
      body {
          font-family: Arial, sans-serif;
          margin: 0;
          padding: 0;
          display: flex;
          flex-direction: column;
          min-height: 100vh;
      }
      header {
          background-color: white;
          color: black;
          padding: 1em 0;
      }
      footer {
          background-color: white;
          color: black;
          text-align: center;
          padding: 1em;
          margin-top: auto;
      }
      table {
        width: 100%;
      }
      table,tr,td {
        background-color: white;
      }
      small,.small {
        font-size: 1.5em;
      }
      #Content{
        width: 80%;
        margin-left: 40px;
      }
      .ql-editor ul li{
        list-style-type: disc;
      }
      .ql-editor ol li{
        list-style-type: decimal;
      }
      .ql-editor ol li li{
        list-style-type: lower-alpha;
      }
      .ql-editor ol li li li{
        list-style-type: lower-roman;
      }
     
      .title {
        font-size: 1.6em;
        font-style: italic;
      }
      .ql-editor p {
        font-size: 1.5em;
        margin-bottom: 0.5em;
      }
      span {
        font-size: 1.5em;
      }
      .big {
        font-size: 4.5em;
      }
      .small {
        font-size: 1.5em;
      }
      .logo{
        vertical-align: top;
      }
      .company{
        font-family: 'Times New Roman', Times, serif;
      }
      .address {
        font-size: 1.5em;
        border: 1px solid gray;
        padding: 10px;        
        width: 60%;
      }
      .subject {
        font-size: 1.7em;
        font-weight: bold;
      }
      .info-line {
        border-top: 1px solid gray;
        border-bottom: 1px solid gray;
      }
      .sign {
        font-size: 1.5em;
        padding-top: 10px;
        border-top: 1px solid gray;
        text-align: center;
      }
      .footer {
        border-top: 1px solid gray;
        padding-top: 5px;
        text-align: left;
      }
      .center {
        text-align: center;
      }
      .weblink{
        color: blue;
        text-decoration: underline;
        font-weight: bold;
      }
    </style>
  </head>
  <body>    


