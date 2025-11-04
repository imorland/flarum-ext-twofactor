import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Stream from 'flarum/common/utils/Stream';
import User from 'flarum/common/models/User';
import type Mithril from 'mithril';
export interface TwoFactorEnableModalAttrs extends IInternalModalAttrs {
    user: User;
    forced: boolean;
    onEnabled: () => void | null;
}
export default class TwoFactorEnableModal extends Modal<TwoFactorEnableModalAttrs> {
    user: User;
    status: string;
    qrCodeUrl: string | null;
    backupCodes: Array<string>;
    token: Stream<string>;
    code: string | null;
    activeTab: string;
    loading: boolean;
    originalHide: () => void;
    oninit(vnode: Mithril.Vnode<TwoFactorEnableModalAttrs>): void;
    static get isDismissibleViaCloseButton(): boolean;
    static get isDismissibleViaEscKey(): boolean;
    static get isDismissibleViaBackdropClick(): boolean;
    className(): string;
    title(): string | any[];
    oncreate(vnode: Mithril.Vnode<TwoFactorEnableModalAttrs>): void;
    onupdate(): void;
    content(): JSX.Element;
    verifyToken(): void;
    finish(): void;
    onSubmit(e: any): void;
}
