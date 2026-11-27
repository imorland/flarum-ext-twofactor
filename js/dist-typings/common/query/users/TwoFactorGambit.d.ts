import { BooleanGambit } from 'flarum/common/query/IGambit';
export default class TwoFactorGambit extends BooleanGambit {
    key(): string;
    canonicalKey(): string;
    filterKey(): string;
    enabled(): boolean;
}
