# This file has been generated by node2nix 1.9.0. Do not edit!

{nodeEnv, fetchurl, fetchgit, nix-gitignore, stdenv, lib, globalBuildInputs ? []}:

let
  sources = {};
in
{
  "pnpm-^6" = nodeEnv.buildNodePackage {
    name = "pnpm";
    packageName = "pnpm";
    version = "6.32.1";
    src = fetchurl {
      url = "https://registry.npmjs.org/pnpm/-/pnpm-6.32.1.tgz";
      sha512 = "vca5B8nbIHSCEIaV7UG1C15v54txazI9u3HhQ0OJad2u8DSzBaFnWiHN7D6EoDg6t5bu/yC1/lShtt+0uFJXFg==";
    };
    buildInputs = globalBuildInputs;
    meta = {
      description = "Fast, disk space efficient package manager";
      homepage = "https://pnpm.io";
      license = "MIT";
    };
    production = true;
    bypassCache = true;
    reconstructLock = true;
  };
}
