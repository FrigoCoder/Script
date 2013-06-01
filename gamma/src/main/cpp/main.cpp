
#include <windows.h>

#include "GammaSetter.h"

int WINAPI WinMain (HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR lpCmdLine, int nCmdShow) {
    GammaSetter setter;
    setter.setGamma(0.6);
    Sleep(3000);
    setter.setGamma(1.0);
}
