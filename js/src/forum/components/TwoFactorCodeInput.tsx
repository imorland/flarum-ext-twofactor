import Component from 'flarum/common/Component';
import type Mithril from 'mithril';

export interface TwoFactorCodeInputAttrs {
  placeholder?: string;
  initial?: string;
  /**
   * Called with the entered code when either 6 digits are entered or when the user presses enter.
   */
  onComplete: (code: string) => void;
}

export default class TwoFactorCodeInput extends Component<TwoFactorCodeInputAttrs> {
  value: string = '';

  oninit(vnode: Mithril.Vnode<TwoFactorCodeInputAttrs>) {
    super.oninit(vnode);
    this.value = vnode.attrs.initial || '';
  }

  oninput(e: Event) {
    const target = e.target as HTMLInputElement;
    this.value = target.value;

    // Auto-submit when exactly 6 digits are entered.
    if (this.value.length === 6) {
      this.attrs.onComplete(this.value);
    }
  }

  onkeydown(e: KeyboardEvent) {
    if (e.key === 'Enter') {
      e.preventDefault();
      this.attrs.onComplete(this.value);
    }
  }

  view(): Mithril.Children {
    return (
      <input
        type="text"
        className="FormControl"
        value={this.value}
        placeholder={this.attrs.placeholder || 'Enter 6-digit code'}
        oninput={(e: Event) => this.oninput(e)}
        onkeydown={(e: KeyboardEvent) => this.onkeydown(e)}
        inputmode="numeric"
        pattern="[0-9]*"
        autocomplete="one-time-code"
      />
    );
  }
}
