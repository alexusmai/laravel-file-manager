## Integration

> See examples in /examples folder

### CKEditor 4

Add to CKEditor config

```js
CKEDITOR.replace('editor-id', {filebrowserImageBrowseUrl: '/file-manager/ckeditor'});
```
  
OR in to the config.js

```js
CKEDITOR.editorConfig = function( config ) {
  
  //...
  
  // Upload image
  config.filebrowserImageBrowseUrl = '/file-manager/ckeditor';
};
```
  
After these actions, you will be able to call the file manager from the CKEditor editor menu (Image -> Selection on the server).
The file manager will appear in a new window.

### TinyMCE 4

Add to TinyMCE configuration

```js
tinymce.init({
      selector: '#my-textarea',
      // ...
      file_browser_callback: function(field_name, url, type, win) {
        tinyMCE.activeEditor.windowManager.open({
          file: '/file-manager/tinymce',
          title: 'Laravel File Manager',
          width: window.innerWidth * 0.8,
          height: window.innerHeight * 0.8,
          resizable: 'yes',
          close_previous: 'no',
        }, {
          setUrl: function(url) {
            win.document.getElementById(field_name).value = url;
          },
        });
      },
    });
```

### SummerNote

Create and add new button

```js
// File manager button (image icon)
const FMButton = function(context) {
  const ui = $.summernote.ui;
  const button = ui.button({
    contents: '<i class="note-icon-picture"></i> ',
    tooltip: 'File Manager',
    click: function() {
      window.open('/file-manager/summernote', 'fm', 'width=1400,height=800');
    }
  });
  return button.render();
};

$('#summernote').summernote({
  toolbar: [
    // [groupName, [list of button]]
    // your settings
    ['fm-button', ['fm']],
  ],
  buttons: {
    fm: FMButton
  }
});
```

And add this function

```js
// set file link
function fmSetLink(url) {
  $('#summernote').summernote('insertImage', url);
}
```

See example - /examples/summernote.blade.php

### Standalone button

Add button

```html
<div class="input-group">
    <input type="text" id="image_label" class="form-control" name="image"
           aria-label="Image" aria-describedby="button-image">
    <div class="input-group-append">
        <button class="btn btn-outline-secondary" type="button" id="button-image">Select</button>
    </div>
</div>
```

and js script

```js
document.addEventListener("DOMContentLoaded", function() {

  document.getElementById('button-image').addEventListener('click', (event) => {
    event.preventDefault();

    window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
  });
});

// set file link
function fmSetLink($url) {
  document.getElementById('image_label').value = $url;
}
```

### Modifications

To change standard views(with file manager), publish them.

```bash
php artisan vendor:publish --tag=fm-views
```
  
You will get:

```
resources/views/vendor/file-manager/ckeditor.blade.php
resources/views/vendor/file-manager/tinymce.blade.php
resources/views/vendor/file-manager/summernote.blade.php
resources/views/vendor/file-manager/fmButton.blade.php
```

Now you can change styles and any more..
