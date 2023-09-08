import app from 'flarum/admin/app';
import Component from 'flarum/common/Component';
import GroupBadge from 'flarum/common/components/GroupBadge';
import EditGroupModal from 'flarum/admin/components/EditGroupModal';
import Group from 'flarum/common/models/Group';
import icon from 'flarum/common/helpers/icon';

/**
 * This is just the group bar from the permissions page, extracted into its own component so it can be used again.
 */
export default class ExtractedGroupBar extends Component {
  view() {
    return (
      <div className="PermissionsPage-groups">
        {app.store
          .all<Group>('groups')
          .filter((group) => [Group.GUEST_ID, Group.MEMBER_ID].indexOf(group.id()!) === -1)
          .map((group) => (
            <button className="Button Group" onclick={() => app.modal.show(EditGroupModal, { group })}>
              <GroupBadge group={group} className="Group-icon" label={null} />
              <span className="Group-name">{group.namePlural()}</span>
            </button>
          ))}
        <button className="Button Group Group--add" onclick={() => app.modal.show(EditGroupModal)}>
          {icon('fas fa-plus', { className: 'Group-icon' })}
          <span className="Group-name">{app.translator.trans('core.admin.permissions.new_group_button')}</span>
        </button>
      </div>
    );
  }
}
