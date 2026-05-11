import app from 'flarum/admin/app';
import { extend } from 'flarum/common/extend';
import EditGroupModal from 'flarum/admin/components/EditGroupModal';
import Switch from 'flarum/common/components/Switch';
import Stream from 'flarum/common/utils/Stream';

export default function extendEditGroupModal() {
  extend(EditGroupModal.prototype, 'oninit', function (vnode) {
    this.requires2FA = Stream(this.group.requires2FA() || false);
  });

  extend(EditGroupModal.prototype, 'fields', function (items) {
    items.add(
      '2fa',
      <div className="Form-group">
        <Switch state={this.requires2FA()} onchange={this.requires2FA}>
          {app.translator.trans('ianm-twofactor.admin.edit_group.2fa_label')}
        </Switch>
        <p className="helpText">{app.translator.trans('ianm-twofactor.admin.edit_group.2fa_help')}</p>
      </div>,
      10
    );
  });

  extend(EditGroupModal.prototype, 'submitData', function (data) {
    data.requires2FA = this.requires2FA();

    return data;
  });
}
