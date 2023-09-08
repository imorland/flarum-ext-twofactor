import Component from 'flarum/common/Component';
import icon from 'flarum/common/helpers/icon';

export default class TwoFactorGridItem extends Component {
  view() {
    const { icon: iconName, title, value, action, helpText } = this.attrs;

    return (
      <li className="TwoFactorGrid-item">
        <span className="TwoFactorGrid-icon">{icon(iconName)}</span>
        <div className="TwoFactorGrid-content">
          <span className="TwoFactorGrid-title">{title}</span>
          <span className="TwoFactorGrid-value">{value}</span>
          {helpText && <span className="helpText TwoFactorGrid-helpText">{helpText}</span>}
        </div>
        {action && <span className="TwoFactorGrid-actions">{action}</span>}
      </li>
    );
  }
}
