<div class="modal {{ !empty($class) ? $class : 'modal-popup' }} fade" id="{{ !empty($id) ? $id : 'modal-popup' }}" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ !empty($title) ? $title : '' }}</h4>
            </div>
            <div class="modal-body">{{ !empty($body) ? $body : '' }}</div>
        </div>
    </div>
</div>
