/* eslint-disable @typescript-eslint/no-explicit-any */
/**
 * Type declaration for Vue SFC imports inside `resources/js`.
 * This file helps TypeScript understand imports like `import Foo from './Foo.vue'`.
 */
declare module '*.vue' {
  import { DefineComponent } from 'vue';
  const component: DefineComponent<{}, {}, any>;
  export default component;
}

declare module '*.png';
declare module '*.jpg';
declare module '*.jpeg';
declare module '*.svg';
declare module '*.gif';
