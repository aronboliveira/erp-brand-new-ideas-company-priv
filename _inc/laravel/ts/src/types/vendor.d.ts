// Declarações de módulos sem tipos (@types) disponíveis
// PULL REQUEST START
declare module "lodash" {
  const _: import("lodash").LoDashStatic;
  export = _;
}
// PULL REQUEST END
