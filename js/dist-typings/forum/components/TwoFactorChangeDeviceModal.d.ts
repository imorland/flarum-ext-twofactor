/// <reference types="flarum/@types/translator-icu-rich" />
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Stream from 'flarum/common/utils/Stream';
import User from 'flarum/common/models/User';
import type Mithril from 'mithril';
export interface TwoFactorChangeDeviceModalAttrs extends IInternalModalAttrs {
    user: User;
    onDeviceChanged?: () => void;
}
export default class TwoFactorChangeDeviceModal extends Modal<TwoFactorChangeDeviceModalAttrs> {
    user: User;
    status: string;
    qrCodeUrl: string | null;
    backupCodes: Array<string>;
    currentToken: Stream<string>;
    newToken: Stream<string>;
    code: string | null;
    activeTab: string;
    loading: boolean;
    oninit(vnode: Mithril.Vnode<TwoFactorChangeDeviceModalAttrs>): void;
    className(): string;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    onupdate(): void;
    content(): JSX.Element;
    renderVerifyCurrentDevice(): JSX.Element;
    renderLoadingQR(): JSX.Element;
    renderDisplayQR(): JSX.Element;
    renderBackupCodes(): JSX.Element;
    renderFinal(): JSX.Element;
    onVerifyCurrentDeviceSubmit(e: Event): void;
    verifyCurrentDevice(): void;
    loadQrCode(): void;
    onVerifyNewDeviceSubmit(e: Event): void;
    verifyNewDevice(): void;
    finish(): void;
}
