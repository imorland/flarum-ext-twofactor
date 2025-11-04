import Model from 'flarum/common/Model';
import User from 'flarum/common/models/User';

export default class TwoFactor extends Model {
  isActive = Model.attribute<boolean>('isActive');
  createdAt = Model.attribute('createdAt', Model.transformDate);
  updatedAt = Model.attribute('updatedAt', Model.transformDate);
  user = Model.hasOne<User>('user');
}
