
#include <math.h>
#include <windows.h>

#include "GammaSetter.h"

GammaSetter::GammaSetter () {
    hGDI32 = ::LoadLibrary("gdi32.dll");
    setDeviceGammaRamp = (DeviceGammaRampFunction) GetProcAddress(hGDI32, "SetDeviceGammaRamp");
}

GammaSetter::~GammaSetter () {
    ::FreeLibrary(hGDI32);
}

void GammaSetter::setRamp (WORD ramp[3][256]) {
    HDC hGammaDC = GetDC(NULL);
    setDeviceGammaRamp(hGammaDC, ramp);
    ReleaseDC(NULL, hGammaDC);
}

void GammaSetter::setGamma (double gamma) {
    WORD ramp[3][256];
    for( int i = 0; i < 256; i++ ){
        double brightness = i / 255.0;
        double compensated = pow(brightness, 1.0 / gamma);
        WORD word = compensated * 65535.0 + 0.5;
        ramp[0][i] = word;
        ramp[1][i] = word;
        ramp[2][i] = word;
    }
    setRamp(ramp);
}
