    </div>    
    <script src="js/functions.js"></script>
    <script src="js/onAfterScripts.js"></script>
    <script src="js/dbConnect.js" type="text/javascript"></script>
  </body>
</html>

<!-- Initialize Quill editor -->
<script>
  const quill = new Quill('#editor', {
    modules: {
      history: {          // Enable with custom configurations
        delay: 0,
        maxStack: 500,
        userOnly: true
      },
      syntax: true,
      toolbar: '#toolbar-container',
    },
    placeholder: 'Vložit text...',
    theme: 'snow',
  });

  quill.on(Quill.events.TEXT_CHANGE, update);
  

  function formatDelta(delta) {
    return `${JSON.stringify(delta.ops, null, 2)}`;
  }

  function update(delta) {
    const contents = quill.getContents();
    var contentsDelta = `${formatDelta(contents)}`;
    localStorage.setItem('editorContentDelta', contentsDelta);

    var html = quill.getSemanticHTML();
    var elementContent = document.getElementById('content');
    if (elementContent){
        localStorage.setItem('editorContent', html);
        elementContent.setAttribute('value', html);        
    };
  }

  readContent();

  // Přidání události pro vkládání obrázku
  document.getElementById('insert-image').addEventListener('click', function() {
    var url = prompt('Vložte URL obrázku:');
    if (url) {
      // Vložení obrázku do editoru
      const range = quill.getSelection();
      quill.insertEmbed(range.index, 'image', url, Quill.sources.USER);
    }
  });

  // Event listener pro změnu velikosti obrázku
  document.querySelector('.ql-editor').addEventListener('click', function(event) {
    if (event.target.tagName === 'IMG') {
      var img = event.target;
      var dialog = document.getElementById('image-resize-dialog');
      
      // Nastavení dialogu poblíž obrázku
      dialog.style.display = 'block';
      dialog.style.top = (img.offsetTop + img.clientHeight) + 'px';
      dialog.style.left = img.offsetLeft + 'px';
      
      // Předvyplnění aktuálních rozměrů
      document.getElementById('image-width').value = img.width;
      document.getElementById('image-height').value = img.height;
      
      // Přidání event listeneru na tlačítko
      document.getElementById('apply-image-size').onclick = function() {
        var newWidth = document.getElementById('image-width').value;
        var newHeight = document.getElementById('image-height').value;
        img.width = newWidth;
        img.height = newHeight;
        dialog.style.display = 'none';
      }
    }
  });

  // Skrýt dialog při kliknutí mimo něj
  document.addEventListener('click', function(event) {
    var dialog = document.getElementById('image-resize-dialog');
    if (!dialog.contains(event.target) && event.target.tagName !== 'IMG') {
      dialog.style.display = 'none';
    }
  });

  function readContent(){
      contentsDelta = localStorage.getItem('editorContentDelta') || '';
      quill.setContents(JSON.parse(contentsDelta));
  }

</script>

