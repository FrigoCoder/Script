
#include <cstdio>
#include <cstdlib>
#include <windows.h>

#include "GammaSetter.h"

int WINAPI WinMain (HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nCmdShow) {
    int argc;
    LPWSTR* argv = CommandLineToArgvW(GetCommandLineW(), &argc);

    if( argc < 2 ){
        printf("Sets the decoding gamma level of the entire desktop.\n");
        printf("Usage: gamma gammalevel\n");
        printf("Where gammalevel is a number between 0.23 and 4.45\n");
        printf("For example: gamma 0.6\n");
        return 1;
    }

    double parsed = _wtof(argv[1]);
    double gamma = parsed == 0.0 ? 1.0 : parsed;

    GammaSetter setter;
    setter.setGamma(1.0);
    setter.setGamma(gamma);
}
