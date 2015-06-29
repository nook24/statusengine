<div class="col-xs-12 col-sm-6">
	<span style="line-height: 32px;">
		<?php echo $this->Paginator->counter(__('Page').' {:page} '.__('of').' {:pages} - {:count} '.__('records')); ?>
	</span>
</div>
<div class="col-xs-12 col-sm-6 text-right">
	<?php echo $this->Paginator->pagination([
		'ul' => 'pagination',
	]); ?>
</div>