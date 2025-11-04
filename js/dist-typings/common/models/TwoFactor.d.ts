import Model from 'flarum/common/Model';
import User from 'flarum/common/models/User';
export default class TwoFactor extends Model {
    isActive: () => boolean;
    createdAt: () => Date | null | undefined;
    updatedAt: () => Date | null | undefined;
    user: () => false | User;
}
