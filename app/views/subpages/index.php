<div class="section grey">
  <h2 class="hgroup cf">
    <span class="hgroup-title">
      <?php if($page->isSite()): ?>
      <?php _l('subpages') ?>
      <?php else: ?>
      <?php _l('subpages.index.headline') ?> <a href="<?php echo purl($page, 'show') ?>"><?php __($page->title()) ?></a>
      <?php endif ?>
    </span>
    <span class="hgroup-options shiv shiv-dark shiv-left cf">

      <a class="hgroup-option-left" href="<?php _u($page, 'show') ?>">
        <?php i('arrow-circle-left', 'left') . _l('subpages.index.back') ?>
      </a>

      <?php if($addbutton): ?>
      <a title="+"<?php e($addbutton->modal, ' data-modal') ?> data-shortcut="+" class="hgroup-option-right" href="<?php __($addbutton->url) ?>">
        <?php i('plus-circle', 'left') ?>
        <?php _l('subpages.index.add') ?>
      </a>
      <?php endif ?>

    </span>
  </h2>

  <?php if($page->hasChildren()): ?>
  <div class="grid subpages-grid">

    <div class="grid-item">
      <h3><a href="<?php echo $visible->firstPage() ?>"><?php _l('subpages.index.visible') ?></a></h3>

      <div class="dropzone subpages">
        <div class="items<?php e($sortable, ' sortable') ?>" id="visible-children" data-flip="<?php echo $flip ?>" data-start="<?php echo $visible->start() ?>" data-total="<?php echo $visible->total() ?>">
          <?php foreach($visible->pages() as $subpage): ?>
          <?php echo new Snippet('subpages/subpage', array('subpage' => $subpage)) ?>
          <?php endforeach ?>
        </div>
      </div>

      <?php echo $visible->pagination() ?>

      <?php if(!$visible->total()): ?>
      <div class="subpages-help subpages-help-left marginalia text">
        <?php _l('subpages.index.visible.help') ?>
      </div>
      <?php endif ?>

    </div><!--

 --><div class="grid-item">
      <h3><a href="<?php echo $invisible->firstPage() ?>"><?php _l('subpages.index.invisible') ?></a></h3>

      <div class="dropzone subpages">
        <div class="items<?php e($sortable, ' sortable') ?>" id="invisible-children">

          <?php foreach($invisible->pages() as $subpage): ?>
          <?php echo new Snippet('subpages/subpage', array('subpage' => $subpage)) ?>
          <?php endforeach ?>

        </div>
      </div>

      <?php echo $invisible->pagination() ?>

      <?php if(!$invisible->total()): ?>
      <div class="subpages-help subpages-help-right marginalia text">
        <?php _l('subpages.index.invisible.help') ?>
      </div>
      <?php endif ?>

    </div>

  </div>

  <?php else: ?>

  <div class="instruction">
    <div class="instruction-content">
      <p class="instruction-text"><?php _l('subpages.index.add.first.text') ?></p>
      <a data-shortcut="+" data-modal class="btn btn-rounded" href="<?php echo purl($page, 'add') ?>">
        <?php _l('subpages.index.add.first.button') ?>
      </a>
    </div>
  </div>

  <?php endif ?>

</div>

<script>

$('.main .sortable').sortable({
  connectWith: '.sortable',
  update: function(e, ui) {

    var $this = $(this);

    if($this.attr('id') == 'visible-children') {

      var start = parseInt($this.data('start'));
      var total = $this.data('total');
      var flip  = $this.data('flip');
      var index = $this.find('.item').index(ui.item);
      var id    = ui.item.attr('id');

      if(flip == '1') {
        // if this is an invisible element the 
        // total number of items in the visible list has
        // to be adjusted to get the right result for the
        // sorting number
        if(ui.sender && ui.sender.attr('id') == 'invisible-children') {
          total++;
        }
        var to = total - start - index + 1;
      } else {
        var to = index + start;              
      }

      if(ui.item.parent().attr('id') !== 'invisible-children') {

        $.post(window.location.href, {action: 'sort', id: id, to: to}, function(data) {
          app.content.replace(data);
        });

      }

    }
  },
  receive : function(event, ui) {

    if($(this).attr('id') == 'invisible-children') {

      $.post(window.location.href, {action: 'hide', id: ui.item.attr('id')}, function(data) {
        app.content.replace(data);
      });

    }

  }
}).disableSelection();

</script>