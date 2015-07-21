<div class="section">

  <h2 class="hgroup cf">
    <span class="hgroup-title">
      <?php if($page->isSite()): ?>
      <?php _l('metatags.files') ?>
      <?php else: ?>
      <?php _l('files.index.headline') ?> <a href="<?php echo $back ?>"><?php __($page->title()) ?></a>
      <?php endif ?>
    </span>
    <span class="hgroup-options shiv shiv-dark shiv-left cf">

      <a class="hgroup-option-left" href="<?php echo $back ?>">
        <?php i('arrow-circle-left', 'left') . _l('files.index.back') ?>
      </a>

      <?php if($page->hasFiles()): ?>
      <a data-modal title="+" data-shortcut="+" class="hgroup-option-right" href="<?php echo purl($page, 'upload') ?>">
        <?php i('plus-circle', 'left') . _l('files.index.upload') ?>
      </a>
      <?php endif ?>
    </span>
  </h2>

  <?php if($files->count()): ?>
  <div class="files" data-api="<?php _u($page, 'files') ?>">

    <div class="grid<?php e($sortable, ' sortable') ?>">

      <?php foreach($files as $file): ?><!--
   --><div class="grid-item" id="<?php __($file->filename()) ?>">
        <figure class="file">
          <a class="file-preview file-preview-is-<?php echo $file->type() ?>" href="<?php echo purl($file, 'show') ?>">
            <?php if(fileHasThumbnail($file)): ?>
            <?php echo thumb($file, array('width' => 300, 'height' => '200', 'crop' => true)) ?>
            <?php elseif($file->extension() == 'svg'): ?>
            <img src="<?php echo $file->url() ?>" alt="<?php echo $file->filename() ?>">
            <?php else: ?>
            <span><?php __($file->extension()) ?></span>
            <?php endif ?>
          </a>
          <figcaption class="file-info">
            <a class="file-name cut" href="<?php echo purl($file, 'show') ?>"><?php __($file->filename()) ?></a>
            <a class="file-meta marginalia cut" href="<?php echo purl($file, 'show') ?>"><?php __($file->type() . ' / ' . $file->niceSize()) ?></a>
          </figcaption>
          <nav class="file-options cf">

            <a class="btn btn-with-icon" href="<?php echo purl($file, 'show') ?>">
              <?php i('pencil', 'left') ?><span><?php _l('files.index.edit') ?></span>
            </a>

            <a data-modal data-modal-return-to="<?php echo purl($page, 'files') ?>" class="btn btn-with-icon" href="<?php echo purl($file, 'delete') ?>">
              <?php i('trash-o', 'left') ?><span><?php _l('files.index.delete') ?></span>
            </a>

          </nav>
        </figure>
      </div><!--
   --><?php endforeach ?>

    </div>

  </div>

  <?php else: ?>

  <div class="instruction">
    <div class="instruction-content">
      <p class="instruction-text"><?php echo l('files.index.upload.first.text') ?></p>
      <a data-modal data-shortcut="+" class="btn btn-rounded" href="<?php echo purl($page, 'upload') ?>">
        <?php echo l('files.index.upload.first.button') ?>
      </a>
    </div>
  </div>

  <?php endif ?>

</div>

<script>

var files    = $('.files');
var sortable = files.find('.sortable');
var items    = files.find('.grid-item');
var api      = files.data('api');

if(items.length) {
  sortable.sortable({
    helper: 'clone',
    update: function() {
      $.post(api, {filenames: sortable.sortable('toArray'), action: 'sort'}, function(data) {
        app.content.replace(data, api);        
      });
    }
  }).disableSelection();
}

</script>