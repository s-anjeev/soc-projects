#include <windows.h>

SERVICE_STATUS g_ServiceStatus;
SERVICE_STATUS_HANDLE g_StatusHandle = NULL;
HANDLE g_StopEvent = NULL;

void WINAPI ServiceCtrlHandler(DWORD CtrlCode)
{
    switch (CtrlCode)
    {
        case SERVICE_CONTROL_STOP:

            g_ServiceStatus.dwCurrentState = SERVICE_STOP_PENDING;
            SetServiceStatus(g_StatusHandle, &g_ServiceStatus);

            SetEvent(g_StopEvent);
            return;

        default:
            break;
    }

    SetServiceStatus(g_StatusHandle, &g_ServiceStatus);
}

void WINAPI ServiceMain(DWORD argc, LPTSTR *argv)
{
    g_StatusHandle = RegisterServiceCtrlHandlerA(
        "HelloService",
        ServiceCtrlHandler
    );

    if (g_StatusHandle == NULL)
        return;

    ZeroMemory(&g_ServiceStatus, sizeof(g_ServiceStatus));

    g_ServiceStatus.dwServiceType = SERVICE_WIN32_OWN_PROCESS;
    g_ServiceStatus.dwControlsAccepted = SERVICE_ACCEPT_STOP;
    g_ServiceStatus.dwCurrentState = SERVICE_START_PENDING;

    SetServiceStatus(g_StatusHandle, &g_ServiceStatus);

    g_StopEvent = CreateEvent(
        NULL,
        TRUE,
        FALSE,
        NULL
    );

    if (g_StopEvent == NULL)
    {
        g_ServiceStatus.dwCurrentState = SERVICE_STOPPED;
        SetServiceStatus(g_StatusHandle, &g_ServiceStatus);
        return;
    }

    g_ServiceStatus.dwCurrentState = SERVICE_RUNNING;
    SetServiceStatus(g_StatusHandle, &g_ServiceStatus);

    //
    // Execute once when the service starts
    //
    STARTUPINFOA si;
    PROCESS_INFORMATION pi;

    ZeroMemory(&si, sizeof(si));
    ZeroMemory(&pi, sizeof(pi));

    si.cb = sizeof(si);

    char cmd[] =
        "cmd.exe /c echo System Backup > C:\\Temp\\hello.txt";

    if (CreateProcessA(
            NULL,
            cmd,
            NULL,
            NULL,
            FALSE,
            CREATE_NO_WINDOW,
            NULL,
            NULL,
            &si,
            &pi))
    {
        CloseHandle(pi.hThread);
        CloseHandle(pi.hProcess);
    }

    //
    // Wait until the service is stopped
    //
    WaitForSingleObject(g_StopEvent, INFINITE);

    CloseHandle(g_StopEvent);

    g_ServiceStatus.dwCurrentState = SERVICE_STOPPED;
    SetServiceStatus(g_StatusHandle, &g_ServiceStatus);
}

int main(void)
{
    SERVICE_TABLE_ENTRYA ServiceTable[] =
    {
        { "HelloService", (LPSERVICE_MAIN_FUNCTIONA)ServiceMain },
        { NULL, NULL }
    };

    StartServiceCtrlDispatcherA(ServiceTable);

    return 0;
}