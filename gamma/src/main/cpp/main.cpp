
#include <cstdio>
#include <cstdlib>
#include <windows.h>

#include "GammaSetter.h"

int WINAPI WinMain (HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nCmdShow) {
    int argc;
    LPWSTR* argv = CommandLineToArgvW(GetCommandLineW(), &argc);

    double gamma = 1.0;
    if( argc >= 2 && _wtof(argv[1]) != 0.0 ){
        gamma = _wtof(argv[1]);
    }

    GammaSetter setter;
    setter.setGamma(gamma);
}
