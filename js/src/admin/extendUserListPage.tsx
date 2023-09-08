import { extend } from 'flarum/common/extend';
import UserListPage from 'flarum/admin/components/UserListPage';
import ItemList from 'flarum/common/utils/ItemList';
import User from 'flarum/common/models/User';
import type Mithril from 'mithril';
import icon from 'flarum/common/helpers/icon';

type ColumnData = {
  /**
   * Column title
   */
  name: Mithril.Children;
  /**
   * Component(s) to show for this column.
   */
  content: (user: User) => Mithril.Children;
};

export default function extendUserListPage() {
  extend(UserListPage.prototype, 'columns', function (columns: ItemList<ColumnData>) {
    columns.add(
      '2fa',
      {
        name: '2FA',
        content: (user: User) => {
          return user.twoFactorEnabled() ? <p>{icon('fas fa-shield-alt')}</p> : <p></p>;
        },
      },
      82
    );
  });
}
