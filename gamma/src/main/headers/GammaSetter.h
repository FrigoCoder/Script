
#ifndef GammaRamp_h
#define GammaRamp_h

class GammaSetter {

    public:

        GammaSetter ();
        ~GammaSetter ();
        void setRamp (WORD ramp[3][256]);
        void setGamma (double gamma);

    private:

        typedef BOOL (WINAPI *DeviceGammaRampFunction) (HDC hDC, LPVOID lpRamp);

        HMODULE hGDI32;
        DeviceGammaRampFunction pGetDeviceGammaRamp;
        DeviceGammaRampFunction pSetDeviceGammaRamp;

};

#endif
