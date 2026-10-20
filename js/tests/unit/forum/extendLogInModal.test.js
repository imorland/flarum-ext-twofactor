import bootstrapForum from '@flarum/jest-config/src/bootstrap/forum';
import { jest } from '@jest/globals';
import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import LogInModal from 'flarum/forum/components/LogInModal';
import RequestError from 'flarum/common/utils/RequestError';
import Stream from 'flarum/common/utils/Stream';
import extendLogInModal from '../../../src/forum/extendLogInModal';

// Stands in for another extension's onerror hook, registered before this one as
// flectar/flarum-turnstile is (it resets its widget there).
const otherExtensionOnerror = jest.fn();

beforeAll(() => {
  bootstrapForum();
  app.boot();

  extend(LogInModal.prototype, 'onerror', otherExtensionOnerror);
  extendLogInModal();
  flarum.reg.add('core', 'forum/components/LogInModal', LogInModal);
});

beforeEach(() => {
  otherExtensionOnerror.mockClear();
});

function modal() {
  const modal = Object.create(LogInModal.prototype);

  modal.identification = Stream('uitest');
  modal.password = Stream('secret');
  modal.remember = Stream(false);
  modal.twoFactorToken = Stream('');
  modal.twoFactorRequired = false;
  modal.onready = () => {};

  return modal;
}

function validationError(detail, pointer) {
  const error = new RequestError(422, '', { url: '/login' }, {});
  error.response = { errors: [{ status: '422', code: 'validation_error', detail, source: { pointer: `/data/attributes/${pointer}` } }] };
  error.alert = { type: 'error', content: [detail] };

  return error;
}

describe('LogInModal with two-factor authentication', () => {
  test('asks for the 2FA token, without an error, when one is required', () => {
    const logIn = modal();

    logIn.onerror(validationError('two_factor_required', 'twoFactorToken'));

    expect(logIn.twoFactorRequired).toBe(true);
    expect(logIn.alertAttrs).toBeFalsy();
    expect(otherExtensionOnerror).toHaveBeenCalledTimes(1);
  });

  test('says the 2FA token is incorrect, and clears it, when it is wrong', () => {
    const logIn = modal();
    logIn.twoFactorRequired = true;
    logIn.twoFactorToken('000000');
    const error = validationError('two_factor_incorrect', 'twoFactorToken');

    logIn.onerror(error);

    expect(error.alert.content).toEqual(app.translator.trans('ianm-twofactor.forum.log_in.two_factor_incorrect_message'));
    expect(logIn.alertAttrs).toBe(error.alert);
    expect(logIn.twoFactorToken()).toEqual('');
    expect(otherExtensionOnerror).toHaveBeenCalledTimes(1);
  });

  test('shows other validation errors, such as a failed captcha, unchanged', () => {
    const logIn = modal();
    const error = validationError("We couldn't verify if you're a robot or not.", 'turnstileToken');

    logIn.onerror(error);

    expect(error.alert.content).toEqual(["We couldn't verify if you're a robot or not."]);
    expect(logIn.alertAttrs).toBe(error.alert);
    expect(logIn.twoFactorRequired).toBe(false);
    expect(otherExtensionOnerror).toHaveBeenCalledTimes(1);
  });

  test('leaves wrong login details to core', () => {
    const logIn = modal();
    const error = new RequestError(401, '', { url: '/login' }, {});
    error.alert = { type: 'error', content: 'The default message' };

    logIn.onerror(error);

    expect(error.alert.content).toEqual(app.translator.trans('core.forum.log_in.invalid_login_message'));
    expect(otherExtensionOnerror).toHaveBeenCalledTimes(1);
  });
});

describe('submitting the 2FA token automatically', () => {
  function typeToken(logIn, value) {
    const field = logIn.fields().get('twoFactor');
    const input = field.children.find((child) => child && child.attrs && child.attrs.name === 'twoFactorToken');

    input.attrs.oninput({ currentTarget: { value }, target: { value } });
  }

  function twoFactorModal(extraParams = {}) {
    const logIn = modal();
    logIn.twoFactorRequired = true;
    logIn.onsubmit = jest.fn();
    const loginParams = logIn.loginParams.bind(logIn);
    logIn.loginParams = () => ({ ...loginParams(), ...extraParams });

    return logIn;
  }

  test('submits once six digits are entered', () => {
    const logIn = twoFactorModal();

    typeToken(logIn, '123456');

    expect(logIn.onsubmit).toHaveBeenCalledTimes(1);
  });

  test('waits for a captcha that has not been completed yet', () => {
    const logIn = twoFactorModal({ turnstileToken: null });

    typeToken(logIn, '123456');

    expect(logIn.onsubmit).not.toHaveBeenCalled();
  });

  test('submits once the captcha has been completed', () => {
    const logIn = twoFactorModal({ turnstileToken: 'abc' });

    typeToken(logIn, '123456');

    expect(logIn.onsubmit).toHaveBeenCalledTimes(1);
  });
});
