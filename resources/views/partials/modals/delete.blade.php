<div class="modal modal-danger fade" id="modal-delete" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ trans('modals.modal_delete.title') }}</h4>
            </div>
            <div class="modal-body">
                {{ trans('modals.modal_delete.message') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-md btn-outline" id="delete-btn">
                    <i class="fa fa-trash-o"></i> <span>{{ trans('modals.modal_delete.button.delete') }}</span>
                </button>
                <button type="button" class="btn btn-md btn-outline" data-dismiss="modal">{{ trans('modals.modal_delete.button.cancel') }}</button>
            </div>
        </div>
    </div>
</div>
