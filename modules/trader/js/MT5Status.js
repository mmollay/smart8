import React, { useEffect, useState } from 'react';

const MT5Status = ({ serverId, serverIp, token }) => {
    const [mt5Status, setMt5Status] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const fetchMT5Status = async () => {
            try {
                const response = await fetch(serverIp + "/getMT5Status", {
                    headers: {
                        'Authorization': token,
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                setMt5Status(data);
            } catch (error) {
                console.error('Error fetching MT5 status:', error);
            } finally {
                setIsLoading(false);
            }
        };

        // Verzögerter Start der Abfrage
        setTimeout(() => {
            fetchMT5Status();
        }, 1000); // 1 Sekunde Verzögerung
    }, [serverIp, token]);

    if (isLoading) {
        return <div className="ui active inline tiny loader"></div>;
    }

    const buttonAction = mt5Status?.process?.active ? 'stopMT5' : 'startMT5';
    const buttonValue = mt5Status?.process?.active ? 'Stop MT5' : 'Start MT5';
    const colorClass = mt5Status?.process?.active ? "red" : "green";
    const icon = mt5Status?.process?.active ? "stop" : "play";

    return (
        <button
            onClick={() => window.post_ema(buttonAction, '', serverId)}
            className={`ui ${colorClass} small fluid button`}
        >
            <i className={`${icon} icon`}></i>
            {buttonValue}
        </button>
    );
};

export default MT5Status;